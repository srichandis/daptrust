#!/usr/bin/env python3
"""Decrypt the encrypted Excel Master-sheet file to a plaintext .xlsx on disk.

Usage:
    python3 decrypt_master_excel.py              # write to the same .xlsx path
    python3 decrypt_master_excel.py --output out.xlsx

The file is encrypted with the MASTER_SHEET_PASSWORD environment variable
(default: dap6977). Requires the `msoffcrypto` Python package.
"""

import os
import sys

import msoffcrypto
import openpyxl


def find_excel_file():
    # The encrypted Excel lives at the project root (next to composer.json).
    root = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
    for name in os.listdir(root):
        if name.lower().endswith(".xlsx") and "MASTER" in name.upper():
            return os.path.join(root, name)
    return None


def decrypt(src, dst, password):
    # Decrypt to a temp file first, then replace the source.
    # This avoids the input file being truncated while still open.
    import tempfile, os
    tmp_fd, tmp_path = tempfile.mkstemp(dir=os.path.dirname(os.path.abspath(dst)), prefix='decrypted_')
    try:
        with os.fdopen(tmp_fd, 'wb') as out:
            with open(src, 'rb') as f:
                ms = msoffcrypto.OfficeFile(f)
                ms.load_key(password=password)
                ms.decrypt(out)
        os.replace(tmp_path, dst)
    except BaseException:
        os.unlink(tmp_path)
        raise


def main():
    import argparse

    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--output", help="output path (default: same path, overwritten)")
    ap.add_argument(
        "--password",
        default=os.environ.get("MASTER_SHEET_PASSWORD", "dap6977"),
        help=" decryption password (default: MASTER_SHEET_PASSWORD env var, fallback dap6977)",
    )
    args = ap.parse_args()

    root = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
    src = find_excel_file()
    if src is None:
        print("ERROR: no encrypted Excel Master file found in " + root)
        sys.exit(1)

    dst = args.output or src
    print(f"Decrypting {src} -> {dst} (password=***)")
    try:
        decrypt(src, dst, args.password)
    except Exception as exc:  # noqa: BLE001
        print(f"ERROR: decryption failed: {exc}")
        sys.exit(1)

    # Sanity-check the output by loading it as a workbook.
    try:
        wb = openpyxl.load_workbook(dst, data_only=True)
        print(f"OK: decrypted file contains sheets: {wb.sheetnames}")
    except Exception as exc:  # noqa: BLE001
        print(f"ERROR: decrypted file is not a valid Excel file: {exc}")
        sys.exit(1)


if __name__ == "__main__":
    main()
