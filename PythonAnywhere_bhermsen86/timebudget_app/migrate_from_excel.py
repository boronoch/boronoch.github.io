"""
One-time migration: import the existing Time Budget Excel workbook into
the app's SQLite database.

Usage:
    python migrate_from_excel.py /path/to/Time_Budget_Planner.xlsx /path/to/timebudget.db

Imports:
  - Categories + colors (from the conditional formatting on Time Budget 2)
  - Items (Section 1: Description, Category, Priority, Hrs/Week target)
  - The recurring weekly plan (Section 2)
  - Any dated cycle tabs (e.g. "SEP 2026") as Cycles, with their frozen
    Planned grids and logged Actual entries for each week.
"""
import sys
import sqlite3
import re
from datetime import datetime, timezone
import openpyxl

CATEGORY_COLORS = {
    "Work":         "BDD7EE",
    "Health":       "C6E0B4",
    "Family":       "F8CBAD",
    "Chores":       "D9D9D9",
    "Leisure":      "CCC0DA",
    "Sleep":        "B4C6E7",
    "Errands":      "FFF2CC",
    "Spirituality": "FFE699",
    "Other":        "F2F2F2",
}

PLAN_SHEET = "Time Budget 2"
TB_FIRST, TB_LAST = 3, 34
N_ITEMS = TB_LAST - TB_FIRST + 1  # 32
DAYS_TB = ["J", "K", "L", "M", "N", "O", "P"]  # Mon..Sun columns on Time Budget 2 Section 2


def find_week_blocks(ws):
    """Scan a '4 Week Template'-style sheet and return the row ranges for
    each week block and the Total block, by looking for marker text
    rather than assuming fixed row numbers (layouts have drifted before)."""
    blocks = []
    total_block = None
    for r in range(1, ws.max_row + 1):
        a = ws.cell(row=r, column=1).value
        s = ws.cell(row=r, column=19).value  # column S
        if isinstance(a, str) and a.strip().upper().startswith("WEEK") and "PLANNED" in a.upper():
            week_num = int(re.search(r"WEEK\s*(\d+)", a.upper()).group(1))
            header_row = None
            for hr in range(r + 1, r + 5):
                if ws.cell(row=hr, column=1).value == "Time":
                    header_row = hr
                    break
            data_start = header_row + 1
            totals_row = None
            for tr in range(data_start, data_start + 40):
                if ws.cell(row=tr, column=1).value == "Daily Total":
                    totals_row = tr
                    break
            blocks.append({
                "week": week_num, "title_row": r, "header_row": header_row,
                "data_start": data_start, "data_end": totals_row - 1, "totals_row": totals_row,
            })
        if isinstance(s, str) and "TOTAL" in s.upper() and "4 WEEK" in s.upper():
            header_row = None
            for hr in range(r + 1, r + 5):
                if ws.cell(row=hr, column=19).value == "Description":
                    header_row = hr
                    break
            data_start = header_row + 1
            totals_row = None
            for tr in range(data_start, data_start + 40):
                if ws.cell(row=tr, column=19).value == "TOTALS":
                    totals_row = tr
                    break
            total_block = {
                "title_row": r, "header_row": header_row,
                "data_start": data_start, "data_end": totals_row - 1, "totals_row": totals_row,
            }
    return blocks, total_block


def migrate(xlsx_path, db_path):
    wb = openpyxl.load_workbook(xlsx_path, data_only=True)
    conn = sqlite3.connect(db_path)
    conn.execute("PRAGMA foreign_keys = ON")

    # ---- Categories ----
    cat_ids = {}
    for i, (name, color) in enumerate(CATEGORY_COLORS.items()):
        cur = conn.execute(
            "INSERT OR IGNORE INTO category (name, color_hex, sort_order) VALUES (?,?,?)",
            (name, color, i),
        )
        row = conn.execute("SELECT id FROM category WHERE name=?", (name,)).fetchone()
        cat_ids[name] = row[0]
    conn.commit()

    # ---- Items + live weekly plan (from Time Budget 2) ----
    ws_plan = wb[PLAN_SHEET]
    item_ids = {}   # tb_row -> item_id
    for i in range(N_ITEMS):
        r = TB_FIRST + i
        desc = ws_plan.cell(row=r, column=1).value    # A
        if not desc:
            continue
        cat_name = ws_plan.cell(row=r, column=2).value  # B
        priority = ws_plan.cell(row=r, column=3).value or "Medium"  # C
        target = ws_plan.cell(row=r, column=4).value or 0  # D
        cat_id = cat_ids.get(cat_name)
        cur = conn.execute(
            "INSERT INTO item (description, category_id, priority, weekly_target_hours, sort_order) "
            "VALUES (?,?,?,?,?)",
            (desc, cat_id, priority, float(target), i),
        )
        item_ids[r] = cur.lastrowid
    conn.commit()

    # Weekly plan grid (Section 2 on Time Budget 2): columns J..P = Mon..Sun
    for i in range(N_ITEMS):  # also 32 time slots, rows TB_FIRST..TB_LAST
        r = TB_FIRST + i
        for day_idx, col_letter in enumerate(DAYS_TB):
            col = ord(col_letter) - ord("A") + 1
            val = ws_plan.cell(row=r, column=col).value
            if not val:
                continue
            # find item_id by matching description text
            match_row = None
            for tb_row, desc_row in item_ids.items():
                if ws_plan.cell(row=tb_row, column=1).value == val:
                    match_row = tb_row
                    break
            if match_row is None:
                continue
            slot_index = i
            conn.execute(
                "INSERT OR REPLACE INTO plan_slot (day_of_week, slot_index, item_id) VALUES (?,?,?)",
                (day_idx, slot_index, item_ids[match_row]),
            )
    conn.commit()

    def item_id_by_description(desc):
        row = conn.execute("SELECT id FROM item WHERE description=?", (desc,)).fetchone()
        return row[0] if row else None

    # ---- Dated cycle tabs (e.g. "SEP 2026") ----
    skip_sheets = {PLAN_SHEET, "Read Me", "4 Week Template", "Time Budget 1", "Time Budget Template"}
    for sheet_name in wb.sheetnames:
        if sheet_name in skip_sheets:
            continue
        ws = wb[sheet_name]
        # heuristic: only treat as a cycle tab if it has "WEEK 1 - PLANNED" somewhere
        has_week1 = any(
            isinstance(ws.cell(row=r, column=1).value, str)
            and "WEEK 1" in ws.cell(row=r, column=1).value.upper()
            for r in range(1, min(ws.max_row, 10) + 1)
        )
        if not has_week1:
            continue

        blocks, total_block = find_week_blocks(ws)
        if not blocks:
            continue

        cur = conn.execute(
            "INSERT INTO cycle (label, created_at, is_active) VALUES (?,?,0)",
            (sheet_name, datetime.now(timezone.utc).isoformat()),
        )
        cycle_id = cur.lastrowid

        # Frozen item snapshot for this cycle: derive from the Total block's
        # Description/Category/Target Hrs columns (S/T/U), which is exactly
        # what was frozen in the spreadsheet.
        cycle_item_ids = {}  # description -> cycle_item_id
        if total_block:
            for r in range(total_block["data_start"], total_block["data_end"] + 1):
                desc = ws.cell(row=r, column=19).value       # S
                if not desc:
                    continue
                cat_name = ws.cell(row=r, column=20).value   # T
                target4 = ws.cell(row=r, column=21).value or 0  # U (Target Hrs, 4 wks)
                cur = conn.execute(
                    "INSERT INTO cycle_item (cycle_id, source_item_id, description, category_name, "
                    "category_color, priority, target_hours_per_week) VALUES (?,?,?,?,?,?,?)",
                    (cycle_id, item_id_by_description(desc), desc, cat_name,
                     CATEGORY_COLORS.get(cat_name, "CCCCCC"), None, float(target4) / 4.0),
                )
                cycle_item_ids[desc] = cur.lastrowid
        conn.commit()

        # Per-week Planned + Actual grids
        for b in blocks:
            week_num = b["week"]
            for i in range(b["data_end"] - b["data_start"] + 1):
                r = b["data_start"] + i
                slot_index = i
                for day_idx, col_letter in enumerate(["B", "C", "D", "E", "F", "G", "H"]):
                    col = ord(col_letter) - ord("A") + 1
                    val = ws.cell(row=r, column=col).value
                    if val and val in cycle_item_ids:
                        conn.execute(
                            "INSERT OR REPLACE INTO cycle_plan_slot "
                            "(cycle_id, week_number, day_of_week, slot_index, cycle_item_id) "
                            "VALUES (?,?,?,?,?)",
                            (cycle_id, week_num, day_idx, slot_index, cycle_item_ids[val]),
                        )
                for day_idx, col_letter in enumerate(["K", "L", "M", "N", "O", "P", "Q"]):
                    col = ord(col_letter) - ord("A") + 1
                    val = ws.cell(row=r, column=col).value
                    if val and val in cycle_item_ids:
                        conn.execute(
                            "INSERT OR REPLACE INTO actual_entry "
                            "(cycle_id, week_number, day_of_week, slot_index, cycle_item_id, logged_at) "
                            "VALUES (?,?,?,?,?,?)",
                            (cycle_id, week_num, day_idx, slot_index, cycle_item_ids[val],
                             datetime.now(timezone.utc).isoformat()),
                        )
        conn.commit()
        print(f"Imported cycle tab '{sheet_name}' as cycle_id={cycle_id} "
              f"with {len(cycle_item_ids)} items across {len(blocks)} week blocks")

    # Mark the most recently imported cycle (if any) as active
    row = conn.execute("SELECT id FROM cycle ORDER BY id DESC LIMIT 1").fetchone()
    if row:
        conn.execute("UPDATE cycle SET is_active=1 WHERE id=?", (row[0],))
        conn.commit()

    conn.close()
    print("Migration complete.")


if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python migrate_from_excel.py <workbook.xlsx> <database.db>")
        sys.exit(1)
    migrate(sys.argv[1], sys.argv[2])
