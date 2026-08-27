<?php

/**
 * Format a number using the Indian place-value system.
 *
 * Indian commas: 1,00,000 instead of 100,000
 * Supports: 1,000 → 10,00,000 → 1,00,00,000
 *
 * @param  float|int|string  $number
 * @param  int               $decimals
 * @return string
 */
function indian_number_format($number, int $decimals = 0): string
{
    $number = (float) $number;
    $isNegative = $number < 0;
    $number = abs($number);

    $wholePart = (int) $number;
    $decimalPart = round(($number - $wholePart) * pow(10, $decimals));

    // Convert to string for comma insertion
    $wholeStr = (string) $wholePart;
    $length = strlen($wholeStr);

    if ($length <= 3) {
        $formatted = $wholeStr;
    } else {
        // Last 3 digits
        $formatted = substr($wholeStr, -3);
        $remaining = substr($wholeStr, 0, $length - 3);

        // Groups of 2 from right
        while (strlen($remaining) > 2) {
            $formatted = substr($remaining, -2) . ',' . $formatted;
            $remaining = substr($remaining, 0, -2);
        }
        if (strlen($remaining) > 0) {
            $formatted = $remaining . ',' . $formatted;
        }
    }

    if ($decimals > 0) {
        $formatted .= '.' . str_pad((string) $decimalPart, $decimals, '0', STR_PAD_LEFT);
    }

    return ($isNegative ? '-' : '') . $formatted;
}

/**
 * Format a currency value in Indian notation with ₹ symbol.
 *
 * Uses Cr/L suffix for large amounts, Indian commas for smaller ones.
 *
 * @param  float|int  $value
 * @return string
 */
function indian_money($value): string
{
    $sign = $value < 0 ? '−' : '';
    $abs = abs((float) $value);

    if ($abs >= 10000000) {
        return $sign . '₹' . number_format($abs / 10000000, 2) . ' Cr';
    }

    if ($abs >= 100000) {
        return $sign . '₹' . number_format($abs / 100000, 2) . ' L';
    }

    return $sign . '₹' . indian_number_format($abs, 2);
}
