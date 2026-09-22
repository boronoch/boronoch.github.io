-- Time Budget App schema (SQLite)

CREATE TABLE IF NOT EXISTS user (
    id INTEGER PRIMARY KEY,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS category (
    id INTEGER PRIMARY KEY,
    name TEXT UNIQUE NOT NULL,
    color_hex TEXT NOT NULL DEFAULT 'CCCCCC',
    sort_order INTEGER NOT NULL DEFAULT 0
);

-- The live, editable item list (Time Budget 2 "Section 1")
CREATE TABLE IF NOT EXISTS item (
    id INTEGER PRIMARY KEY,
    description TEXT NOT NULL,
    category_id INTEGER REFERENCES category(id),
    priority TEXT NOT NULL DEFAULT 'Medium',   -- High / Medium / Low
    weekly_target_hours REAL NOT NULL DEFAULT 0,
    active INTEGER NOT NULL DEFAULT 1,
    sort_order INTEGER NOT NULL DEFAULT 0
);

-- The live, editable recurring weekly template (Time Budget 2 "Section 2").
-- day_of_week: 0=Monday .. 6=Sunday. slot_index: 0..N-1, half-hour slots
-- starting at plan_start_hour (see settings table).
CREATE TABLE IF NOT EXISTS plan_slot (
    day_of_week INTEGER NOT NULL,
    slot_index INTEGER NOT NULL,
    item_id INTEGER REFERENCES item(id),
    PRIMARY KEY (day_of_week, slot_index)
);

-- One row per 4-week cycle (e.g. "OCT 2026")
CREATE TABLE IF NOT EXISTS cycle (
    id INTEGER PRIMARY KEY,
    label TEXT NOT NULL,
    start_date TEXT,                 -- ISO date, optional
    created_at TEXT NOT NULL,
    is_active INTEGER NOT NULL DEFAULT 1
);

-- Frozen snapshot of each item AS OF the moment a cycle was created.
-- Everything in a cycle references this, not "item" directly, so later
-- edits to the live item list never retroactively change history.
CREATE TABLE IF NOT EXISTS cycle_item (
    id INTEGER PRIMARY KEY,
    cycle_id INTEGER NOT NULL REFERENCES cycle(id),
    source_item_id INTEGER,          -- traceability only, may be null/stale
    description TEXT NOT NULL,
    category_name TEXT,
    category_color TEXT,
    priority TEXT,
    target_hours_per_week REAL NOT NULL DEFAULT 0
);

-- Each week's Planned grid within a cycle. Starts as a frozen mirror of
-- plan_slot at cycle-creation time; individual cells can then be
-- overridden for a specific week without affecting other weeks.
CREATE TABLE IF NOT EXISTS cycle_plan_slot (
    cycle_id INTEGER NOT NULL REFERENCES cycle(id),
    week_number INTEGER NOT NULL,    -- 1..4
    day_of_week INTEGER NOT NULL,    -- 0..6
    slot_index INTEGER NOT NULL,
    cycle_item_id INTEGER REFERENCES cycle_item(id),
    PRIMARY KEY (cycle_id, week_number, day_of_week, slot_index)
);

-- Each week's Actual grid within a cycle - what you logged really happened.
CREATE TABLE IF NOT EXISTS actual_entry (
    cycle_id INTEGER NOT NULL REFERENCES cycle(id),
    week_number INTEGER NOT NULL,
    day_of_week INTEGER NOT NULL,
    slot_index INTEGER NOT NULL,
    cycle_item_id INTEGER REFERENCES cycle_item(id),
    logged_at TEXT,
    PRIMARY KEY (cycle_id, week_number, day_of_week, slot_index)
);

-- App-wide settings (single row)
CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY CHECK (id = 1),
    plan_start_hour REAL NOT NULL DEFAULT 5.0,   -- 5:00 AM
    plan_end_hour REAL NOT NULL DEFAULT 21.0,    -- 9:00 PM
    slots_per_day INTEGER NOT NULL DEFAULT 32
);

-- Morning Routine checklist state (single row - today's state only).
-- checks_json holds, per column, which task indices are checked:
--   {"Monday": [0,2,5], "TuesdayAMSwim": [1]}
-- Cleared automatically whenever "date" no longer matches today.
CREATE TABLE IF NOT EXISTS routine_state (
    id INTEGER PRIMARY KEY CHECK (id = 1),
    date TEXT NOT NULL,
    selected_column TEXT NOT NULL DEFAULT 'Monday',
    checks_json TEXT NOT NULL DEFAULT '{}'
);

