#!/usr/bin/env python3
"""
Import DAP data from the MASTER sheet of the Excel file.
Handles all entry types including RD, MF, and bank transfers.
"""

import msoffcrypto
import io
import openpyxl
import pymysql
from datetime import datetime

FILE_PATH = 'DAP EDUCATIONAL TRUST MASTER ACCOUNT 2027 R2.xlsx'
PASSWORD = 'dap6977'

DB_CONFIG = {
    'host': '127.0.0.1',
    'port': 3306,
    'user': 'root',
    'password': 'root',
    'database': 'dap',
    'charset': 'utf8mb4',
}

ACCT_MAP = {'cash': 1, 'ab': 2, 'sbi': 3, 'hdfc': 4}


def load_workbook():
    with open(FILE_PATH, 'rb') as f:
        ms = msoffcrypto.OfficeFile(f)
        ms.load_key(password=PASSWORD)
        d = io.BytesIO()
        ms.decrypt(d)
        d.seek(0)
        return openpyxl.load_workbook(d, data_only=True)


def parse_date(s):
    if not s or not isinstance(s, str):
        return None
    for fmt in ('%d.%m.%Y', '%d.%m.%y', '%d/%m/%Y', '%d/%m/%y'):
        try:
            return datetime.strptime(s.strip(), fmt).date()
        except ValueError:
            continue
    # Try parsing as datetime (openpyxl sometimes returns datetime objects)
    if isinstance(s, datetime):
        return s.date()
    return None


def compute_fy(entry_date):
    """Compute Indian financial year string like '2025-26' from a date."""
    if entry_date is None:
        return None
    m = entry_date.month
    y = entry_date.year
    if m >= 4:
        return f"{y}-{str(y + 1)[-2:]}"
    else:
        return f"{y - 1}-{str(y)[-2:]}"


def safe_float(v):
    if v is None:
        return 0.0
    try:
        return float(v)
    except (ValueError, TypeError):
        return 0.0


# Column index (0-based) -> (account_key, entry_type, is_balance)
# Based on the MASTER sheet structure
COLUMN_MAP = {
    # Cash columns
    3:  ('cash', 'donation_income', False),     # CASH DONOR+OD:N
    4:  ('cash', 'bank_transfer', False),        # BANK TRANSFER
    5:  ('cash', 'stationary_charges', False),   # STATIONARY CHARGES
    6:  ('cash', 'donation_expense', False),     # CASH DONATION
    7:  ('cash', None, True),                    # CASH BALANCE (skip)

    # AB (Andhra Bank) columns
    8:  ('ab', 'donation_income', False),        # AB DONOR
    9:  ('ab', 'donation_expense', False),       # AB DONATION
    10: ('ab', 'bank_charges', False),           # AB BANK CHARGES
    11: ('ab', 'bank_interest', False),          # AB BANK INTEREST
    12: ('ab', 'fd_interest', False),            # AB FD INTEREST
    13: ('ab', 'rd_interest', False),            # AB RD INTEREST
    14: ('ab', 'mf_interest', False),            # AB MF INTEREST
    15: ('ab', 'professional_charges', False),   # AB PROFESSIONAL CHARGES
    16: ('ab', 'stationary_charges', False),     # AB STATIONARY CHARGES
    17: ('ab', 'bank_transfer', False),          # AB BANK TRANSFER
    18: ('ab', 'it_return', False),              # AB IT RETURN
    19: ('ab', 'fd_maturity', False),            # FD MATURITY AMOUNT (AB)
    20: ('ab', 'rd_maturity', False),            # RD MATURITY AMOUNT (AB)
    21: ('ab', 'mf_maturity', False),            # MF MATURITY AMOUNT (AB)
    22: ('ab', 'fd_investment', False),          # FD - AB
    23: ('ab', 'rd_investment', False),          # RD - AB
    24: ('ab', 'mf_investment', False),          # MF - AB
    25: ('ab', None, True),                      # ANDHRA BANK BALANCE (skip)

    # SBI columns
    26: ('sbi', 'donation_income', False),       # DONOR - SBI
    27: ('sbi', 'donation_expense', False),      # DONATION - SBI
    28: ('sbi', 'bank_charges', False),          # SBI BANK CHARGES
    29: ('sbi', 'bank_interest', False),         # SBI BANK INTEREST
    30: ('sbi', 'fd_interest', False),           # SBI FD INTEREST
    31: ('sbi', 'rd_interest', False),           # SBI RD INTEREST
    32: ('sbi', 'mf_interest', False),           # SBI MF INTEREST
    33: ('sbi', 'professional_charges', False),  # SBI PROFESSIONAL CHARGES
    34: ('sbi', 'stationary_charges', False),    # SBI STATIONARY CHARGES
    35: ('sbi', 'bank_transfer', False),         # SBI BANK TRANSFER
    36: ('sbi', 'it_return', False),             # SBI IT RETURN
    37: ('sbi', 'fd_maturity', False),           # FD MATURITY AMOUNT (SBI)
    38: ('sbi', 'rd_maturity', False),           # RD MATURITY AMOUNT (SBI)
    39: ('sbi', 'mf_maturity', False),           # MF MATURITY AMOUNT (SBI)
    40: ('sbi', 'fd_investment', False),         # FD - SBI
    41: ('sbi', 'rd_investment', False),         # RD - SBI
    42: ('sbi', 'mf_investment', False),         # MF - SBI
    43: ('sbi', None, True),                     # SBI BANK BALANCE (skip)

    # HDFC columns
    44: ('hdfc', 'donation_income', False),      # DONOR - HDFC
    45: ('hdfc', 'donation_expense', False),     # DONATION - HDFC
    46: ('hdfc', 'bank_charges', False),         # HDFC BANK CHARGES
    47: ('hdfc', 'bank_interest', False),        # HDFC BANK INTEREST
    48: ('hdfc', 'fd_interest', False),          # HDFC FD INTEREST
    49: ('hdfc', 'rd_interest', False),          # HDFC RD INTEREST
    50: ('hdfc', 'mf_interest', False),          # HDFC MF INTEREST
    51: ('hdfc', 'professional_charges', False), # HDFC PROFESSIONAL CHARGES
    52: ('hdfc', 'stationary_charges', False),   # HDFC STATIONARY CHARGES
    53: ('hdfc', 'bank_transfer', False),        # HDFC BANK TRANSFER
    54: ('hdfc', 'it_return', False),            # HDFC IT RETURN
    55: ('hdfc', 'fd_maturity', False),          # FD MATURITY AMOUNT (HDFC)
    56: ('hdfc', 'rd_maturity', False),          # RD MATURITY AMOUNT (HDFC)
    57: ('hdfc', 'mf_maturity', False),          # MF MATURITY AMOUNT (HDFC)
    58: ('hdfc', 'fd_investment', False),        # FD - HDFC
    59: ('hdfc', 'rd_investment', False),        # RD - HDFC
    60: ('hdfc', 'mf_investment', False),        # MF - HDFC
    61: ('hdfc', None, True),                    # HDFC BANK BALANCE (skip)

    # Other
    63: ('sbi', 'donation_to_ops', False),      # DONATION TO OPS SAIVA PERAVAI
}


def find_data_end(ws):
    """Find the last row of real transaction data (before summary/duplicate sections)."""
    last_valid = None
    saw_blank = False
    for r in range(1, ws.max_row + 1):
        row = list(ws.iter_rows(min_row=r, max_row=r, values_only=True))[0]
        date_val = row[1] if len(row) > 1 else None
        if date_val is None:
            if last_valid is not None:
                saw_blank = True
            continue
        s = str(date_val).strip()
        if parse_date(s) is not None:
            if saw_blank:
                # We hit a valid date after a blank row — this is a duplicate/summary section
                return last_valid
            last_valid = r
    return last_valid or ws.max_row


def parse_master_sheet(ws):
    """Parse the MASTER sheet and return entries + transfers."""
    # Find header row (row with DATE in column B)
    header_row = 1
    for r in range(1, 6):
        for cell in ws[r]:
            if cell.value and str(cell.value).strip().upper() == 'DATE':
                header_row = r
                break

    data_start = header_row + 1  # Row after header (first data row might be C/F balance)
    data_end = find_data_end(ws)
    print(f"  Data range: rows {data_start} to {data_end}")

    entries = []
    transfers = []

    for row_idx in range(data_start, data_end + 1):
        row = list(ws.iter_rows(min_row=row_idx, max_row=row_idx, values_only=True))[0]
        if len(row) < 3:
            continue

        date_val = row[1]   # Column B
        desc_val = row[2]   # Column C

        if not date_val:
            continue

        # Skip C/F balance rows
        if isinstance(desc_val, str) and 'C/F' in desc_val.upper():
            continue

        # Parse date
        entry_date = parse_date(str(date_val))
        if entry_date is None:
            continue

        description = str(desc_val).strip() if desc_val else ''
        fy = compute_fy(entry_date)

        # Collect transfer columns for this row
        row_transfers = []

        for col_idx, (acct, etype, is_bal) in COLUMN_MAP.items():
            if is_bal or etype is None:
                continue
            if col_idx >= len(row):
                continue

            val = safe_float(row[col_idx])
            if val == 0:
                continue

            if etype == 'bank_transfer':
                row_transfers.append((entry_date, description, acct, val, fy))
            else:
                donor = description if etype == 'donation_income' else None
                entries.append({
                    'entry_type': etype,
                    'entry_date': entry_date,
                    'financial_year': fy,
                    'account': acct,
                    'amount': abs(val),
                    'particulars': description,
                    'donor_name': donor,
                })

        # Handle transfers: pair them up by matching amounts on different accounts
        used = set()
        for i, (dt, desc, src, amt, fy) in enumerate(row_transfers):
            if i in used:
                continue
            for j, (dt2, desc2, src2, amt2, fy2) in enumerate(row_transfers):
                if j in used or j == i:
                    continue
                if src != src2 and abs(amt) == abs(amt2):
                    transfers.append({
                        'entry_type': 'bank_transfer',
                        'entry_date': dt,
                        'financial_year': fy,
                        'account': src,
                        'to_account': src2,
                        'amount': abs(amt),
                        'particulars': desc,
                    })
                    used.add(i)
                    used.add(j)
                    break
            else:
                # No match found - single-sided transfer
                transfers.append({
                    'entry_type': 'bank_transfer',
                    'entry_date': dt,
                    'financial_year': fy,
                    'account': src,
                    'to_account': None,
                    'amount': abs(amt),
                    'particulars': desc,
                })
                used.add(i)

    return entries, transfers


def main():
    print("Loading Excel...")
    wb = load_workbook()

    if 'MASTER' not in wb.sheetnames:
        print("ERROR: MASTER sheet not found!")
        return

    ws = wb['MASTER']
    print(f"MASTER sheet: {ws.max_row} rows, {ws.max_column} cols")

    conn = pymysql.connect(**DB_CONFIG)
    cur = conn.cursor()

    print("Clearing existing data...")
    cur.execute("DELETE FROM entries")
    cur.execute("ALTER TABLE entries AUTO_INCREMENT = 1")
    conn.commit()
    print("Entries cleared.")

    print("Parsing MASTER sheet...")
    entries, transfers = parse_master_sheet(ws)
    print(f"  Found {len(entries)} entries + {len(transfers)} transfers")

    # Insert entries (non-transfers)
    if entries:
        entry_sql = """INSERT INTO entries
            (entry_type, entry_date, financial_year, account_id, amount, particulars,
             donor_name, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, NOW(), NOW())"""

        entry_rows = []
        for e in entries:
            entry_rows.append((
                e['entry_type'], e['entry_date'], e['financial_year'],
                ACCT_MAP.get(e['account']), e['amount'],
                e['particulars'], e.get('donor_name'),
            ))
        cur.executemany(entry_sql, entry_rows)
        conn.commit()
        print(f"Inserted {len(entry_rows)} entries")

    # Insert transfers (with to_account_id)
    if transfers:
        transfer_sql = """INSERT INTO entries
            (entry_type, entry_date, financial_year, account_id, to_account_id, amount, particulars,
             created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, NOW(), NOW())"""

        transfer_rows = []
        for t in transfers:
            transfer_rows.append((
                t['entry_type'], t['entry_date'], t['financial_year'],
                ACCT_MAP.get(t['account']),
                ACCT_MAP.get(t['to_account']) if t['to_account'] else None,
                t['amount'], t['particulars'],
            ))
        cur.executemany(transfer_sql, transfer_rows)
        conn.commit()
        print(f"Inserted {len(transfer_rows)} transfers")

    # Verify
    cur.execute("SELECT COUNT(*) FROM entries")
    total = cur.fetchone()[0]
    print(f"\nTotal entries in DB: {total}")

    # Breakdown by type
    cur.execute("SELECT entry_type, COUNT(*) as cnt FROM entries GROUP BY entry_type ORDER BY cnt DESC")
    print("\nBy type:")
    for etype, cnt in cur.fetchall():
        print(f"  {etype}: {cnt}")

    # Breakdown by account
    cur.execute("""
        SELECT a.name, COUNT(*) as cnt
        FROM entries e
        JOIN accounts a ON e.account_id = a.id
        GROUP BY a.name
        ORDER BY cnt DESC
    """)
    print("\nBy account:")
    for name, cnt in cur.fetchall():
        print(f"  {name}: {cnt}")

    # Breakdown by financial year
    cur.execute("SELECT financial_year, COUNT(*) as cnt FROM entries GROUP BY financial_year ORDER BY financial_year")
    print("\nBy financial year:")
    for fy, cnt in cur.fetchall():
        print(f"  {fy}: {cnt}")

    cur.close()
    conn.close()
    print("\nDone!")


if __name__ == '__main__':
    main()
