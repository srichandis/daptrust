#!/usr/bin/env python3
"""
Re-import DAP data with proper transfer handling.
Bank transfers create ONE entry with account_id (source) and to_account_id (dest).
"""

import msoffcrypto
import io
import openpyxl
import pymysql
from datetime import datetime

FILE_PATH = '/Users/srichandskaushik/Documents/source/dap/DAP EDUCATIONAL TRUST MASTER ACCOUNT 2027 R2.xlsx'
PASSWORD = 'dap6977'

DB_CONFIG = {
    'host': '127.0.0.1',
    'port': 3306,
    'user': 'root',
    'password': 'root',
    'database': 'dap',
    'charset': 'utf8mb4',
}

# Account keys: cash, ab (Andhra Bank), sbi (SBI)
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
    for fmt in ('%d.%m.%Y', '%d.%m.%y'):
        try:
            return datetime.strptime(s.strip(), fmt).date()
        except ValueError:
            continue
    return None


def safe_float(v):
    if v is None:
        return 0.0
    try:
        return float(v)
    except (ValueError, TypeError):
        return 0.0


def find_header_row(ws):
    for r in range(1, 6):
        for cell in ws[r]:
            if cell.column_letter == 'B' and cell.value and str(cell.value).strip().upper() == 'DATE':
                return r
    return 3


def build_col_map(ws):
    """Map column letter -> (account_key, entry_type, is_balance)."""
    HEADER = {
        'CASH DONOR': ('cash', 'donation_income', False),
        'BANK TRANSFER': ('cash', 'bank_transfer', False),
        'STATIONARY CHARGES': ('cash', 'stationary_charges', False),
        'CASH DONATION': ('cash', 'donation_expense', False),
        'CASH BALANCE': ('cash', None, True),
        'AB DONOR': ('ab', 'donation_income', False),
        'AB DONATION': ('ab', 'donation_expense', False),
        'AB BANK CHARGES': ('ab', 'bank_charges', False),
        'AB BANK INTEREST': ('ab', 'bank_interest', False),
        'AB FD INTEREST': ('ab', 'fd_interest', False),
        'AB PROFESSIONAL CHARGES': ('ab', 'professional_charges', False),
        'AB STATIONARY CHARGES': ('ab', 'stationary_charges', False),
        'AB BANK TRANSFER': ('ab', 'bank_transfer', False),
        'AB IT RETURN': ('ab', 'it_return', False),
        'FD - AB': ('ab', 'fd_investment', False),
        'ANDHRA BANK BALANCE': ('ab', None, True),
        'DONOR - SBI': ('sbi', 'donation_income', False),
        'DONATION - SBI': ('sbi', 'donation_expense', False),
        'SBI BANK CHARGES': ('sbi', 'bank_charges', False),
        'SBI BANK INTEREST': ('sbi', 'bank_interest', False),
        'SBI FD INTEREST': ('sbi', 'fd_interest', False),
        'SBI PROFESSIONAL CHARGES': ('sbi', 'professional_charges', False),
        'SBI STATIONARY CHARGES': ('sbi', 'stationary_charges', False),
        'SBI BANK TRANSFER': ('sbi', 'bank_transfer', False),
        'SBI IT RETURN': ('sbi', 'it_return', False),
        'FD - SBI': ('sbi', 'fd_investment', False),
        'SBI BANK BALANCE': ('sbi', None, True),
        'DONOR - HDFC': ('hdfc', 'donation_income', False),
        'DONATION - HDFC': ('hdfc', 'donation_expense', False),
        'HDFC BANK CHARGES': ('hdfc', 'bank_charges', False),
        'HDFC BANK INTEREST': ('hdfc', 'bank_interest', False),
        'HDFC FD INTEREST': ('hdfc', 'fd_interest', False),
        'HDFC PROFESSIONAL CHARGES': ('hdfc', 'professional_charges', False),
        'HDFC STATIONARY CHARGES': ('hdfc', 'stationary_charges', False),
        'HDFC BANK TRANSFER': ('hdfc', 'bank_transfer', False),
        'HDFC IT RETURN': ('hdfc', 'it_return', False),
        'FD - HDFC': ('hdfc', 'fd_investment', False),
        'HDFC BANK BALANCE': ('hdfc', None, True),
        'DONATION TO OPS SAIVA PERAVAI': ('sbi', 'donation_expense', False),
    }
    # "FD MATURITY AMOUNT" appears multiple times
    fd_maturity_count = 0

    cols = []
    header_row = find_header_row(ws)
    for cell in ws[header_row]:
        if cell.value is None:
            continue
        name = str(cell.value).strip()
        letter = cell.column_letter
        idx = cell.column - 1

        if name == 'FD MATURITY AMOUNT':
            acct = ['ab', 'sbi', 'hdfc'][fd_maturity_count] if fd_maturity_count < 3 else 'ab'
            cols.append((letter, acct, 'fd_maturity', False))
            fd_maturity_count += 1
            continue
        if 'CASH DONOR' in name and 'CASH DONATION' not in name:
            cols.append((letter, 'cash', 'donation_income', False))
            continue

        if name in HEADER:
            acct, etype, bal = HEADER[name]
            cols.append((letter, acct, etype, bal))

    return cols


def parse_sheet(ws, fy_name):
    col_map = build_col_map(ws)
    header_row = find_header_row(ws)
    data_start = header_row + 2

    entries = []  # Non-transfer entries
    transfers = []  # Transfer entries: list of (date, desc, source_acct, amount)

    for row_idx in range(data_start, ws.max_row + 1):
        row = ws[row_idx]
        date_val = row[1].value if len(row) > 1 else None  # B
        desc_val = row[2].value if len(row) > 2 else None  # C

        if not date_val:
            continue
        if isinstance(desc_val, str) and 'C/F' in desc_val.upper():
            continue

        entry_date = parse_date(str(date_val))
        if entry_date is None:
            continue

        description = str(desc_val).strip() if desc_val else ''

        # Collect transfer columns for this row
        row_transfers = []

        for letter, acct, etype, is_bal in col_map:
            if is_bal or etype is None:
                continue
            # Convert letter to 0-based index
            col_idx = openpyxl.utils.column_index_from_string(letter) - 1
            if col_idx >= len(row):
                continue
            val = safe_float(row[col_idx].value)
            if val == 0:
                continue

            if etype == 'bank_transfer':
                row_transfers.append((entry_date, description, acct, val))
            else:
                donor = description if etype == 'donation_income' else None
                entries.append({
                    'entry_type': etype,
                    'entry_date': entry_date,
                    'financial_year': fy_name,
                    'account': acct,
                    'amount': abs(val),
                    'particulars': description,
                    'donor_name': donor,
                })

        # Now handle transfers: pair them up
        # Strategy: match transfers within the same row by amount
        # Cash->AB, Cash->SBI, AB->SBI
        used = set()
        for i, (dt, desc, src, amt) in enumerate(row_transfers):
            if i in used:
                continue
            # Find a matching transfer on a different account with same amount
            for j, (dt2, desc2, src2, amt2) in enumerate(row_transfers):
                if j in used or j == i:
                    continue
                if src != src2 and amt == amt2:
                    # Pair them: src is source, src2 is dest
                    # Priority: Cash->AB, Cash->SBI, AB->SBI
                    transfers.append({
                        'entry_type': 'bank_transfer',
                        'entry_date': dt,
                        'financial_year': fy_name,
                        'account': src,
                        'to_account': src2,
                        'amount': amt,
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
                    'financial_year': fy_name,
                    'account': src,
                    'to_account': None,
                    'amount': amt,
                    'particulars': desc,
                })
                used.add(i)

    return entries, transfers


def main():
    print("Loading Excel...")
    wb = load_workbook()

    conn = pymysql.connect(**DB_CONFIG)
    cur = conn.cursor()

    print("Clearing existing data...")
    cur.execute("DELETE FROM entries")
    cur.execute("DELETE FROM accounts")
    cur.execute("ALTER TABLE accounts AUTO_INCREMENT = 1")
    cur.execute("ALTER TABLE entries AUTO_INCREMENT = 1")

    # Create accounts
    accounts = [
        (1, 'Cash Account', 'cash', None, None, None, None, 'none', 0),
        (2, 'Andhra Bank', 'bank', 'Andhra Bank', None, None, None, 'none', 0),
        (3, 'State Bank of India', 'bank', 'SBI', None, None, None, 'none', 0),
        (4, 'HDFC Bank', 'bank', 'HDFC', None, None, None, 'none', 0),
    ]
    for aid, name, atype, bn, br, an, ifsc, it, ob in accounts:
        cur.execute(
            """INSERT INTO accounts (id, name, type, bank_name, branch, account_number, ifsc,
               investment_type, opening_balance, is_active, created_at, updated_at)
               VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, 1, NOW(), NOW())""",
            (aid, name, atype, bn, br, an, ifsc, it, ob)
        )
    conn.commit()
    print("Accounts created.")

    all_entries = []
    all_transfers = []

    fy_sheets = [s for s in wb.sheetnames if s.startswith('FY')]
    for fy in fy_sheets:
        ws = wb[fy]
        entries, transfers = parse_sheet(ws, fy)
        all_entries.extend(entries)
        all_transfers.extend(transfers)
        print(f"  {fy}: {len(entries)} entries + {len(transfers)} transfers")

    # Insert entries
    entry_sql = """INSERT INTO entries
        (entry_type, entry_date, financial_year, account_id, amount, particulars,
         donor_name, created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, %s, %s, NOW(), NOW())"""

    entry_rows = []
    for e in all_entries:
        entry_rows.append((
            e['entry_type'], e['entry_date'], e['financial_year'],
            ACCT_MAP.get(e['account']), e['amount'],
            e['particulars'], e.get('donor_name'),
        ))
    if entry_rows:
        cur.executemany(entry_sql, entry_rows)
        conn.commit()
    print(f"Inserted {len(entry_rows)} entries")

    # Insert transfers (with to_account_id)
    transfer_sql = """INSERT INTO entries
        (entry_type, entry_date, financial_year, account_id, to_account_id, amount, particulars,
         created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, %s, %s, NOW(), NOW())"""

    transfer_rows = []
    for t in all_transfers:
        transfer_rows.append((
            t['entry_type'], t['entry_date'], t['financial_year'],
            ACCT_MAP.get(t['account']),
            ACCT_MAP.get(t['to_account']) if t['to_account'] else None,
            t['amount'], t['particulars'],
        ))
    if transfer_rows:
        cur.executemany(transfer_sql, transfer_rows)
        conn.commit()
    print(f"Inserted {len(transfer_rows)} transfers")

    # Verify
    cur.execute("SELECT COUNT(*) FROM entries")
    print(f"\nTotal entries: {cur.fetchone()[0]}")

    cur.execute("SELECT name, opening_balance FROM accounts ORDER BY id")
    for name, ob in cur.fetchall():
        print(f"  {name}: opening=₹{ob}")

    cur.close()
    conn.close()
    print("\nDone!")


if __name__ == '__main__':
    main()
