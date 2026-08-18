#!/usr/bin/env python3
"""
Import dapacc.xlsx (MASTER sheet) into the dap MySQL database.

The MASTER sheet is the consolidated trust ledger (FY02 -> FY26). Every
non-zero, non-balance cell becomes an Entry row, mapped onto the Laravel
entry_type vocabulary:

  *DONOR*                       -> donation_income (received)
  *DONATION*                    -> donation_expense (given away)
  *BANK CHARGES*                -> bank_charges
  *BANK INTEREST*               -> bank_interest
  *FD/RD/MF INTEREST*           -> fd/rd/mf_interest
  *PROFESSIONAL CHARGES*        -> professional_charges
  *STATIONARY CHARGES*          -> stationary_charges
  *IT RETURN*                   -> it_return
  *BANK TRANSFER*               -> bank_transfer (paired per date)
  *MATURITY AMOUNT*             -> fd/rd/mf_maturity (interest from same row)
  *FD/RD/MF - <bank>*           -> fd/rd/mf_investment
  *BALANCE* columns             -> skipped (derived running balances)
  OPS column (col 63)           -> skipped (duplicate of the *DONATION* columns)

Usage:
    python3 import_dapacc.py            # generate import_dapacc.sql and load it
    python3 import_dapacc.py --yes      # allow overwriting existing entries
    python3 import_dapacc.py --dry-run  # only print a summary
"""

import argparse
import datetime
import os
import subprocess
import sys
from collections import Counter, defaultdict

import openpyxl

HERE = os.path.dirname(os.path.abspath(__file__))
XLSX = os.path.join(HERE, "dapacc.xlsx")
SQL_FILE = os.path.join(HERE, "import_dapacc.sql")
DB_NAME = "dap"

# (account name, mysql-account-type, bank_name, column range)
# Each range ends with that account's BALANCE column, which is skipped later.
BLOCKS = [
    ("Cash", "cash", "Cash", range(3, 8)),          # CASH BALANCE at 7
    ("Andhra Bank", "bank", "Andhra Bank", range(8, 26)),    # ANDHRA BANK BALANCE at 25
    ("SBI", "bank", "State Bank of India", range(26, 44)),   # SBI BANK BALANCE at 43
    ("HDFC", "bank", "HDFC Bank", range(44, 62)),            # HDFC BANK BALANCE at 61
]
TRANSFER_COLS = {4: "Cash", 17: "Andhra Bank", 35: "SBI", 53: "HDFC"}
BALANCE_COLS = [7, 25, 43, 61]  # Cash, Andhra Bank, SBI, HDFC running balances

MATURITY_TYPES = {"FD": "fd_maturity", "RD": "rd_maturity", "MF": "mf_maturity"}
INTEREST_TYPES = {"FD": "fd_interest", "RD": "rd_interest", "MF": "mf_interest"}
INVEST_TYPES = {"FD": "fd_investment", "RD": "rd_investment", "MF": "mf_investment"}


def parse_date(v):
    if isinstance(v, datetime.datetime):
        return v.date()
    if isinstance(v, datetime.date):
        return v
    if isinstance(v, str):
        s = v.strip()
        for fmt in ("%d.%m.%Y", "%d.%m.%y", "%d-%m-%Y", "%d/%m/%Y", "%d/%m/%y"):
            try:
                return datetime.datetime.strptime(s, fmt).date()
            except ValueError:
                continue
    return None


def financial_year(d):
    year = d.year if d.month >= 4 else d.year - 1
    return f"FY{year % 100:02d}"


def classify(header):
    """Map a MASTER column header to an entry_type (or 'transfer'/'skip')."""
    h = (header or "").strip()
    if not h:
        return "skip"
    if "BALANCE" in h:
        return "skip"
    if "BANK TRANSFER" in h:
        return "transfer"
    if "MATURITY AMOUNT" in h:
        return MATURITY_TYPES.get(h.split()[0], "skip")
    if "FD INTEREST" in h:
        return "fd_interest"
    if "RD INTEREST" in h:
        return "rd_interest"
    if "MF INTEREST" in h:
        return "mf_interest"
    if "BANK CHARGES" in h:
        return "bank_charges"
    if "BANK INTEREST" in h:
        return "bank_interest"
    if "PROFESSIONAL CHARGES" in h:
        return "professional_charges"
    if "STATIONARY CHARGES" in h:
        return "stationary_charges"
    if "IT RETURN" in h:
        return "it_return"
    if "DONOR" in h:
        return "donation_income"
    if "DONATION" in h:
        return "donation_expense"
    for prefix in ("FD", "RD", "MF"):
        if h.startswith(prefix + " "):
            return INVEST_TYPES[prefix]
    return "skip"


def num(v):
    """Return a float, or None if the cell is empty/non-numeric/zero."""
    if v is None or isinstance(v, bool):
        return None
    if isinstance(v, (int, float)):
        return float(v) if v != 0 else None
    s = str(v).strip().replace(",", "")
    if not s:
        return None
    try:
        f = float(s)
        return f if f != 0 else None
    except ValueError:
        return None


def scheme_for(entry_type):
    return {t: p for p, t in MATURITY_TYPES.items()}.get(entry_type) or \
        {t: p for p, t in INVEST_TYPES.items()}.get(entry_type)


def sql_escape(s):
    return str(s).replace("\\", "\\\\").replace("'", "''")


def build_entries():
    wb = openpyxl.load_workbook(XLSX, read_only=True, data_only=True)
    ws = wb["MASTER"]
    rows = list(ws.iter_rows(values_only=True))
    header = rows[0]

    # Pre-compute per-column mapping: (account, entry_type)
    col_map = {}
    for account, _atype, _bank, cols in BLOCKS:
        for ci in cols:
            col_map[ci] = (account, classify(header[ci]))

    entries = []
    skipped_rows = 0
    transfer_rows = []  # (date, account, signed_value)

    for r in rows[1:]:
        d = parse_date(r[1])
        desc = str(r[2]).strip() if r[2] is not None else ""
        if d is None:
            continue  # header / totals rows
        if desc.upper() == "C/F BALANCE":
            continue  # carry-forward (opening balance) rows

        # Rows without a balance cell are the redundant "TAX REFUND" appendix
        # at the bottom of the sheet (its entries already appear in the main
        # ledger's IT RETURN columns) — skip them to avoid double counting.
        if not any(isinstance(r[ci], (int, float)) for ci in BALANCE_COLS):
            continue

        row_entries = []

        # --- transaction cells ---------------------------------------------
        maturity_interest = defaultdict(list)  # "account:FD" -> [values]
        for ci, (account, etype) in col_map.items():
            if etype in ("skip", "transfer"):
                continue
            v = num(r[ci])
            if v is None:
                continue
            if etype in MATURITY_TYPES.values():
                row_entries.append({
                    "entry_type": etype, "account": account, "amount": v,
                    "scheme": scheme_for(etype), "particulars": desc,
                })
            elif etype in INTEREST_TYPES.values():
                prefix = etype.split("_")[0].upper()
                maturity_interest[f"{account}:{prefix}"].append(v)
            else:
                row_entries.append({
                    "entry_type": etype, "account": account, "amount": v,
                    "particulars": desc,
                    "donor_name": desc if etype == "donation_income" else None,
                })

        # Fold same-row interest into maturity entries (principal + interest).
        folded = set()
        for e in row_entries:
            if e["entry_type"] in MATURITY_TYPES.values():
                prefix = e["entry_type"].split("_")[0].upper()
                key = f"{e['account']}:{prefix}"
                if key in maturity_interest and key not in folded:
                    folded.add(key)
                    e["interest_amount"] = sum(maturity_interest.pop(key))

        # Remaining interest cells become standalone interest entries.
        for key, vals in maturity_interest.items():
            account, prefix = key.split(":")
            for v in vals:
                row_entries.append({
                    "entry_type": INTEREST_TYPES[prefix], "account": account,
                    "amount": v, "particulars": desc,
                })

        # --- transfers -----------------------------------------------------
        for ci, account in TRANSFER_COLS.items():
            v = num(r[ci])
            if v is not None:
                transfer_rows.append((d, account, v))

        if row_entries:
            fy = financial_year(d)
            for e in row_entries:
                e["entry_date"] = d
                e["financial_year"] = fy
                entries.append(e)
        else:
            skipped_rows += 1

    # --- pair transfers -----------------------------------------------------
    by_date = defaultdict(list)
    for d, account, v in transfer_rows:
        by_date[d].append((account, v))

    def make_transfer(d, src, dst, amount):
        entries.append({
            "entry_type": "bank_transfer", "entry_date": d,
            "financial_year": financial_year(d),
            "account": src, "to_account": dst, "amount": amount,
            "particulars": "Bank transfer",
        })

    unmatched = []
    for d, lst in sorted(by_date.items()):
        negs = [(a, -v) for a, v in lst if v < 0]
        poss = [(a, v) for a, v in lst if v > 0]

        # 1) exact-amount matches within the same date
        for na, nv in list(negs):
            for pa, pv in list(poss):
                if abs(nv - pv) < 0.01:
                    make_transfer(d, na, pa, nv)
                    negs.remove((na, nv))
                    poss.remove((pa, pv))
                    break

        # 2) leftovers: exact-amount match across other dates
        for na, nv in list(negs):
            for od, olst in by_date.items():
                if od == d:
                    continue
                for oa, ov in olst:
                    if ov > 0 and abs(ov - nv) < 0.01:
                        make_transfer(d, na, oa, nv)
                        negs.remove((na, nv))
                        break
                if (na, nv) not in negs:
                    break
        for pa, pv in list(poss):
            for od, olst in by_date.items():
                if od == d:
                    continue
                for oa, ov in olst:
                    if ov < 0 and abs(-ov - pv) < 0.01:
                        make_transfer(d, oa, pa, pv)
                        poss.remove((pa, pv))
                        break
                if (pa, pv) not in poss:
                    break

        # 3) whatever remains is unbalanced.
        #    Unmatched inflows are still recorded (source unknown) so that the
        #    receiving account's balance stays faithful to the workbook.
        for pa, pv in poss:
            make_transfer(d, None, pa, pv)
            unmatched.append(f"{d}: transfer in {pa} {pv} recorded without a known source")
        for na, nv in negs:
            unmatched.append(f"{d}: unmatched transfer out {na} {nv} skipped")

    return entries, unmatched, skipped_rows


def write_sql(entries, account_ids):
    with open(SQL_FILE, "w") as f:
        f.write("SET NAMES utf8mb4;\n")
        f.write("-- Generated by import_dapacc.py\n")
        f.write("TRUNCATE TABLE entries;\n")
        f.write("DELETE FROM accounts;\n")
        f.write("ALTER TABLE accounts AUTO_INCREMENT = 1;\n")

        f.write("INSERT INTO accounts (name, type, bank_name, investment_type, opening_balance, is_active, created_at, updated_at) VALUES\n")
        f.write(",\n".join(
            f"('{sql_escape(name)}', '{atype}', '{sql_escape(bank)}', 'none', 0, 1, NOW(), NOW())"
            for name, atype, bank, _ in BLOCKS
        ) + ";\n")

        f.write("INSERT INTO entries (entry_type, entry_date, financial_year, account_id, to_account_id, amount, particulars, donor_name, scheme_name, interest_amount, bank_charges_amount, notes, created_at, updated_at) VALUES\n")
        batch = []
        for e in entries:
            donor = f"'{sql_escape(e['donor_name'])}'" if e.get("donor_name") else "NULL"
            scheme = f"'{sql_escape(e['scheme'])}'" if e.get("scheme") else "NULL"
            interest = e.get("interest_amount")
            interest_sql = f"{interest:.2f}" if interest is not None else "NULL"
            batch.append(
                f"('{e['entry_type']}', '{e['entry_date']}', '{e['financial_year']}', "
                f"{account_ids[e['account']] if e.get('account') else 'NULL'}, "
                f"{account_ids[e['to_account']] if e.get('to_account') else 'NULL'}, "
                f"{e['amount']:.2f}, "
                f"'{sql_escape(e.get('particulars') or '')}', {donor}, {scheme}, {interest_sql}, NULL, "
                f"'Imported from dapacc.xlsx', NOW(), NOW())"
            )
        f.write(",\n".join(batch) + ";\n")
    print(f"Wrote {SQL_FILE} with {len(entries)} entries")


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--dry-run", action="store_true")
    ap.add_argument("--yes", action="store_true",
                    help="overwrite existing entries/accounts without prompting")
    args = ap.parse_args()

    account_ids = {account: i + 1 for i, (account, _, _, _) in enumerate(BLOCKS)}

    entries, unmatched, skipped = build_entries()

    types = Counter(e["entry_type"] for e in entries)
    years = Counter(e["financial_year"] for e in entries)
    accounts = Counter(e["account"] for e in entries)
    total = sum(abs(e["amount"]) for e in entries)

    print(f"Total entries to import: {len(entries)}")
    print(f"Rows skipped (no transactions): {skipped}")
    print(f"Sum of absolute amounts: ₹{total:,.2f}")
    print("\nBy type:")
    for t, c in sorted(types.items()):
        print(f"  {t:24s} {c}")
    print("\nBy financial year:")
    for y, c in sorted(years.items()):
        print(f"  {y}  {c}")
    print("\nBy account:")
    for a, c in sorted((k, v) for k, v in accounts.items() if k):
        print(f"  {a:14s} {c}")

    if unmatched:
        print(f"\n{len(unmatched)} unmatched transfer(s):")
        for u in unmatched[:20]:
            print("  ", u)

    if args.dry_run:
        return

    # Guard against silently overwriting hand-entered data on re-runs.
    if not args.yes:
        try:
            res = subprocess.run(
                f"mysql -u root -proot {DB_NAME} -N -e 'SELECT COUNT(*) FROM entries;'",
                shell=True, capture_output=True, text=True)
            existing = int(res.stdout.strip() or 0) if res.returncode == 0 else 0
        except ValueError:
            existing = 0
        if existing > 0:
            print(f"The {DB_NAME} database already has {existing} entries."
                  f" Re-run with --yes to wipe and re-import, or use --dry-run.")
            sys.exit(1)

    write_sql(entries, account_ids)

    cmd = f"mysql -u root -proot {DB_NAME} < {SQL_FILE}"
    print("\nLoading into MySQL:", cmd)
    res = subprocess.run(cmd, shell=True, capture_output=True, text=True)
    if res.returncode != 0:
        print("MySQL load FAILED:")
        print(res.stderr)
        sys.exit(1)
    print("MySQL load OK")


if __name__ == "__main__":
    main()
