<?php

namespace App\Services;

use App\Models\Entry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MergeService
{
    /**
     * Normalize a name for comparison: lowercase, trim, collapse spaces, remove common prefixes.
     */
    private function normalize(string $name): string
    {
        $name = mb_strtolower(trim($name));
        // Remove dots, commas, extra spaces
        $name = preg_replace('/[.,]/', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        // Remove common honorifics/prefixes
        $name = preg_replace('/^(shri|smt|dr|mr|mrs|ms|prof|rev)\s+/i', '', $name);
        // Remove common suffixes like "(OPS)", "(KGS)"
        $name = preg_replace('/\s*\([^)]*\)\s*/', ' ', $name);
        $name = trim($name);
        return $name;
    }

    /**
     * Extract the 'core' name part, ignoring single-letter initials.
     * "D.ANANDAPADMANABAN" → "anandapadmanaban"
     * "ANANDAPADMANABAN.D" → "anandapadmanaban"
     * "V. THIRUMALAI" → "thirumalai"
     */
    private function coreName(string $normalized): string
    {
        $words = preg_split('/\s+/', $normalized);
        // Filter out single-letter words (initials)
        $core = array_filter($words, fn ($w) => mb_strlen($w) > 1);
        return implode(' ', $core);
    }

    /**
     * For single-word names like "danandapadmanaban" or "anandapadmanaband",
     * extract the long part (the actual name) by removing leading/trailing
     * single-letter segments.
     * "danandapadmanaban" → "anandapadmanaban"
     * "anandapadmanaband" → "anandapadmanaban"
     */
    private function extractLongPart(string $singleWord): string
    {
        if (mb_strlen($singleWord) <= 2) {
            return $singleWord;
        }
        // Try removing leading single char
        $noLeading = preg_replace('/^[a-z]/', '', $singleWord);
        // Try removing trailing single char
        $noTrailing = preg_replace('/[a-z]$/', '', $singleWord);
        // Pick the longer result (the one that removed less)
        return mb_strlen($noLeading) >= mb_strlen($noTrailing) ? $noLeading : $noTrailing;
    }

    /**
     * Sort words in a name for word-order-independent comparison.
     * "P Anand" and "Anand P" both become "anand p".
     */
    private function sortedWords(string $name): string
    {
        $words = preg_split('/\s+/', $name);
        sort($words, SORT_STRING | SORT_FLAG_CASE);
        return implode(' ', $words);
    }

    /**
     * Name similarity that handles word reordering.
     * Compares sorted words using Jaccard + Levenshtein blend.
     */
    private function similarity(string $a, string $b): float
    {
        if ($a === $b) {
            return 1.0;
        }

        // Exact match after sorting words
        $sortedA = $this->sortedWords($a);
        $sortedB = $this->sortedWords($b);
        if ($sortedA === $sortedB) {
            return 1.0;
        }

        // Check if core names (ignoring initials) match
        $coreA = $this->coreName($a);
        $coreB = $this->coreName($b);
        if ($coreA !== '' && $coreB !== '' && $coreA === $coreB) {
            return 0.95;
        }

        // Handle missing spaces: "atuljain" vs "atul jain"
        // Remove all spaces and compare
        $flatA = str_replace(' ', '', $a);
        $flatB = str_replace(' ', '', $b);
        if ($flatA === $flatB && mb_strlen($flatA) >= 3) {
            return 0.95;
        }

        // For single-word names, try extracting the long part
        // e.g. "danandapadmanaban" → "anandapadmanaban"
        $wordsA = preg_split('/\s+/', $a);
        $wordsB = preg_split('/\s+/', $b);
        if (count($wordsA) === 1 && count($wordsB) === 1) {
            $longA = $this->extractLongPart($wordsA[0]);
            $longB = $this->extractLongPart($wordsB[0]);
            if ($longA === $longB && mb_strlen($longA) >= 3) {
                return 0.95;
            }
            // Also try: does removing leading/trailing char from A give something
            // that's a prefix/suffix of B (handles DANAND vs ANAND)?
            $noLeadA = preg_replace('/^[a-z]/', '', $wordsA[0]);
            $noTrailA = preg_replace('/[a-z]$/', '', $wordsA[0]);
            $noLeadB = preg_replace('/^[a-z]/', '', $wordsB[0]);
            $noTrailB = preg_replace('/[a-z]$/', '', $wordsB[0]);
            foreach ([$noLeadA, $noTrailA] as $strippedA) {
                foreach ([$noLeadB, $noTrailB] as $strippedB) {
                    if ($strippedA !== '' && $strippedB !== '' && $strippedA === $strippedB && mb_strlen($strippedA) >= 3) {
                        return 0.95;
                    }
                }
            }
        }

        // One core is a substring of the other (handles "subramani" vs "subramanian")
        if ($coreA !== '' && $coreB !== '' && (str_contains($coreA, $coreB) || str_contains($coreB, $coreA))) {
            $shorter = min(mb_strlen($coreA), mb_strlen($coreB));
            $longer = max(mb_strlen($coreA), mb_strlen($coreB));
            if ($shorter / $longer >= 0.7) {
                return 0.85;
            }
        }

        // Word-level Jaccard similarity
        $wordsA = array_unique(explode(' ', $sortedA));
        $wordsB = array_unique(explode(' ', $sortedB));
        $intersection = count(array_intersect($wordsA, $wordsB));
        $union = count(array_unique(array_merge($wordsA, $wordsB)));
        $jaccard = $union > 0 ? $intersection / $union : 0;

        // Levenshtein on sorted versions
        $maxLen = max(mb_strlen($sortedA), mb_strlen($sortedB));
        $levenshtein = $maxLen > 0 ? 1.0 - (levenshtein($sortedA, $sortedB) / $maxLen) : 1.0;

        // Also check if one name is a substring of the other
        $substring = 0;
        if (str_contains($a, $b) || str_contains($b, $a)) {
            $substring = 0.8;
        }

        // Blend: 50% Jaccard + 30% Levenshtein + 20% substring
        return ($jaccard * 0.5) + ($levenshtein * 0.3) + ($substring * 0.2);
    }

    /**
     * Find groups of entries with similar donor/particulars names.
     *
     * Returns a Collection of groups, each group being:
     * [
     *     'canonical' => string,      // the proposed merged name
     *     'entries'   => Collection,   // Entry models in this group
     *     'total'     => float,        // sum of amounts
     * ]
     */
    public function findSimilarGroups(
        ?string $entryType = null,
        ?int $accountId = null,
        float $threshold = 0.75,
        int $minGroupSize = 2,
    ): Collection {
        // Only group entries that have a donor_name (donation income entries)
        $query = Entry::query()
            ->select('id', 'particulars', 'donor_name', 'amount', 'account_id', 'entry_type', 'entry_date', 'financial_year')
            ->where('entry_type', 'donation_income')
            ->whereNotNull('donor_name')
            ->where('donor_name', '!=', '');

        if ($entryType) {
            $query->where('entry_type', $entryType);
        }
        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        $entries = $query->get();

        // Build normalized name map
        $nameMap = [];
        foreach ($entries as $entry) {
            // Only use donor_name for grouping
            $rawName = $entry->donor_name;
            if (empty($rawName) || $rawName === '0') {
                continue;
            }
            $normalized = $this->normalize($rawName);
            if (strlen($normalized) < 2) {
                continue;
            }
            $nameMap[$entry->id] = [
                'raw' => $rawName,
                'normalized' => $normalized,
                'entry' => $entry,
            ];
        }

        // Union-Find for grouping
        $parent = array_keys($nameMap);
        $parent = array_combine($parent, $parent);

        $find = function (&$parent, $x) {
            $root = $x;
            while ($parent[$root] !== $root) {
                $root = $parent[$root];
            }
            // Path compression
            while ($parent[$x] !== $root) {
                $next = $parent[$x];
                $parent[$x] = $root;
                $x = $next;
            }
            return $root;
        };

        // Compare pairs (optimization: group by first 3 chars first)
        $buckets = [];
        foreach ($nameMap as $id => $info) {
            $key = substr($info['normalized'], 0, 3);
            $buckets[$key][] = $id;
        }

        // Only compare within same bucket or adjacent buckets
        foreach ($buckets as $key => $ids) {
            // Compare within bucket
            for ($i = 0; $i < count($ids); $i++) {
                for ($j = $i + 1; $j < count($ids); $j++) {
                    $a = $nameMap[$ids[$i]]['normalized'];
                    $b = $nameMap[$ids[$j]]['normalized'];
                    if ($this->similarity($a, $b) >= $threshold) {
                        $rootI = $find($parent, $ids[$i]);
                        $rootJ = $find($parent, $ids[$j]);
                        if ($rootI !== $rootJ) {
                            $parent[$rootI] = $rootJ;
                        }
                    }
                }
            }
            // Compare with adjacent buckets (±1)
            for ($delta = 1; $delta <= 1; $delta++) {
                $adjacentKey = substr($key, 0, 2) . chr(ord(substr($key, 2, 1)) + $delta);
                if (isset($buckets[$adjacentKey])) {
                    foreach ($ids as $idA) {
                        foreach ($buckets[$adjacentKey] as $idB) {
                            $a = $nameMap[$idA]['normalized'];
                            $b = $nameMap[$idB]['normalized'];
                            if ($this->similarity($a, $b) >= $threshold) {
                                $rootI = $find($parent, $idA);
                                $rootJ = $find($parent, $idB);
                                if ($rootI !== $rootJ) {
                                    $parent[$rootI] = $rootJ;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Build groups
        $groups = [];
        foreach ($nameMap as $id => $info) {
            $root = $find($parent, $id);
            $groups[$root][] = $id;
        }

        // Filter by minimum group size and build result
        $result = collect();
        foreach ($groups as $memberIds) {
            if (count($memberIds) < $minGroupSize) {
                continue;
            }

            $entriesInGroup = collect($memberIds)->map(fn ($id) => $nameMap[$id]['entry']);
            $canonicals = $entriesInGroup->map(fn ($e) => $e->donor_name ?: $e->particulars)->unique();

            // Pick the most common name as canonical
            $nameCounts = $entriesInGroup->map(fn ($e) => $e->donor_name ?: $e->particulars)
                ->countBy()
                ->sortDesc();
            $canonical = $nameCounts->keys()->first();

            // Skip groups where all names are already identical (no merging needed)
            $uniqueNames = $entriesInGroup->map(fn ($e) => $e->donor_name)->unique()->values();
            if ($uniqueNames->count() <= 1) {
                continue;
            }

            $result->push([
                'canonical' => $canonical,
                'entries' => $entriesInGroup->sortBy('entry_date'),
                'total' => $entriesInGroup->sum('amount'),
                'count' => $entriesInGroup->count(),
                'unique_names' => $uniqueNames,
            ]);
        }

        return $result->sortByDesc('count');
    }

    /**
     * Merge a group of entries: rename all entries to the canonical name.
     */
    public function mergeGroup(array $entryIds, string $canonicalName): int
    {
        return Entry::whereIn('id', $entryIds)->update([
            'donor_name' => $canonicalName,
        ]);
    }
}
