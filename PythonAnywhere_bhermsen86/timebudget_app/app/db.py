import sqlite3
import os
from flask import g, current_app

DAYS = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"]


def get_db():
    if "db" not in g:
        g.db = sqlite3.connect(current_app.config["DATABASE"])
        g.db.row_factory = sqlite3.Row
        g.db.execute("PRAGMA foreign_keys = ON")
    return g.db


def close_db(e=None):
    db = g.pop("db", None)
    if db is not None:
        db.close()


def init_db(app):
    db_path = app.config["DATABASE"]
    is_new = not os.path.exists(db_path)
    conn = sqlite3.connect(db_path)
    schema_path = os.path.join(os.path.dirname(__file__), "schema.sql")
    with open(schema_path, "r") as f:
        conn.executescript(f.read())
    conn.execute(
        "INSERT OR IGNORE INTO settings (id, plan_start_hour, plan_end_hour, slots_per_day) "
        "VALUES (1, 5.0, 21.0, 32)"
    )
    conn.commit()
    conn.close()
    return is_new


def slot_time_label(slot_index, plan_start_hour=5.0):
    total_minutes = int(plan_start_hour * 60) + slot_index * 30
    hour24 = (total_minutes // 60) % 24
    minute = total_minutes % 60
    suffix = "AM" if hour24 < 12 else "PM"
    hour12 = hour24 % 12
    if hour12 == 0:
        hour12 = 12
    return f"{hour12}:{minute:02d} {suffix}"


def register_db(app):
    app.teardown_appcontext(close_db)
