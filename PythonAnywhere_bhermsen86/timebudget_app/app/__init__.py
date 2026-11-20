import os
import json
import secrets
from datetime import datetime, timezone, date, timedelta
from zoneinfo import ZoneInfo

from flask import Flask, render_template, request, redirect, url_for, session, flash, jsonify
from werkzeug.security import generate_password_hash, check_password_hash

from . import db as dbmod
from .db import DAYS, slot_time_label, cycle_date, today_week_day

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


def central_today_iso():
    return datetime.now(ZoneInfo("America/Chicago")).date().isoformat()


def _load_or_create_secret_key():
    """Use TIMEBUDGET_SECRET if set; otherwise persist a generated key to a
    file so sessions survive app restarts/reloads (needed for long-lived
    'stay logged in' sessions to actually stay valid)."""
    env_key = os.environ.get("TIMEBUDGET_SECRET")
    if env_key:
        return env_key
    path = os.path.join(BASE_DIR, "secret_key.txt")
    if os.path.exists(path):
        with open(path) as f:
            return f.read().strip()
    key = secrets.token_hex(32)
    with open(path, "w") as f:
        f.write(key)
    return key


def create_app():
    app = Flask(__name__)
    app.config["DATABASE"] = os.environ.get(
        "TIMEBUDGET_DB", os.path.join(BASE_DIR, "timebudget.db")
    )
    app.config["SECRET_KEY"] = _load_or_create_secret_key()
    app.config["PERMANENT_SESSION_LIFETIME"] = timedelta(days=90)
    app.config["SESSION_COOKIE_SECURE"] = os.environ.get("TIMEBUDGET_INSECURE_COOKIE") != "1"
    app.config["SESSION_COOKIE_SAMESITE"] = "Lax"

    dbmod.register_db(app)
    with app.app_context():
        dbmod.init_db(app)

    # ---------------- auth helpers ----------------
    def login_required(view):
        from functools import wraps

        @wraps(view)
        def wrapped(*args, **kwargs):
            if not session.get("user_id"):
                return redirect(url_for("login", next=request.path))
            return view(*args, **kwargs)
        return wrapped

    def get_settings():
        db = dbmod.get_db()
        return db.execute("SELECT * FROM settings WHERE id=1").fetchone()

    def sync_new_items_into_cycle(cycle_id):
        """Any live item that doesn't yet have a cycle_item row in this
        cycle gets added (using its current live values). This never
        touches items already frozen into the cycle - only adds ones
        that are missing, so items added mid-cycle become loggable
        without disturbing anything already recorded."""
        db = dbmod.get_db()
        missing = db.execute(
            "SELECT i.*, c.name as category_name, c.color_hex as category_color "
            "FROM item i LEFT JOIN category c ON c.id = i.category_id "
            "WHERE i.active=1 AND i.id NOT IN "
            "(SELECT source_item_id FROM cycle_item WHERE cycle_id=? AND source_item_id IS NOT NULL)",
            (cycle_id,),
        ).fetchall()
        for it in missing:
            db.execute(
                "INSERT INTO cycle_item (cycle_id, source_item_id, description, category_name, "
                "category_color, priority, target_hours_per_week) VALUES (?,?,?,?,?,?,?)",
                (cycle_id, it["id"], it["description"], it["category_name"],
                 it["category_color"], it["priority"], it["weekly_target_hours"]),
            )
        if missing:
            db.commit()

    def get_active_cycle():
        db = dbmod.get_db()
        cycle = db.execute("SELECT * FROM cycle WHERE is_active=1 ORDER BY id DESC LIMIT 1").fetchone()
        if cycle:
            sync_new_items_into_cycle(cycle["id"])
        return cycle

    def cycle_items(cycle_id):
        db = dbmod.get_db()
        return db.execute(
            "SELECT * FROM cycle_item WHERE cycle_id=? ORDER BY id", (cycle_id,)
        ).fetchall()

    def week_summary(cycle_id, week_number):
        """Return list of dicts: description, category, color, planned, actual, variance."""
        db = dbmod.get_db()
        items = cycle_items(cycle_id)
        rows = []
        for it in items:
            planned = db.execute(
                "SELECT COUNT(*) FROM cycle_plan_slot WHERE cycle_id=? AND week_number=? AND cycle_item_id=?",
                (cycle_id, week_number, it["id"]),
            ).fetchone()[0] * 0.5
            actual = db.execute(
                "SELECT COUNT(*) FROM actual_entry WHERE cycle_id=? AND week_number=? AND cycle_item_id=?",
                (cycle_id, week_number, it["id"]),
            ).fetchone()[0] * 0.5
            rows.append({
                "id": it["id"], "description": it["description"],
                "category": it["category_name"], "color": it["category_color"],
                "priority": it["priority"],
                "planned": planned, "actual": actual, "variance": round(planned - actual, 2),
            })
        return rows

    def total_summary(cycle_id):
        db = dbmod.get_db()
        items = cycle_items(cycle_id)
        rows = []
        for it in items:
            actual = db.execute(
                "SELECT COUNT(*) FROM actual_entry WHERE cycle_id=? AND cycle_item_id=?",
                (cycle_id, it["id"]),
            ).fetchone()[0] * 0.5
            planned = db.execute(
                "SELECT COUNT(*) FROM cycle_plan_slot WHERE cycle_id=? AND cycle_item_id=?",
                (cycle_id, it["id"]),
            ).fetchone()[0] * 0.5
            target4 = it["target_hours_per_week"] * 4
            rows.append({
                "id": it["id"], "description": it["description"],
                "category": it["category_name"], "color": it["category_color"],
                "priority": it["priority"],
                "target": target4, "planned": planned, "actual": actual,
                "variance": round(target4 - actual, 2),
            })
        return rows

    def slots_elapsed_today(settings):
        """How many half-hour slots of the plan day have fully elapsed, as of
        right now (Central Time). 0 before the plan day starts, full
        slots_per_day after it ends."""
        now = datetime.now(ZoneInfo("America/Chicago"))
        start_hour = settings["plan_start_hour"]
        start_dt = now.replace(
            hour=int(start_hour), minute=int(round((start_hour % 1) * 60)),
            second=0, microsecond=0,
        )
        elapsed_minutes = (now - start_dt).total_seconds() / 60
        if elapsed_minutes <= 0:
            return 0
        return min(settings["slots_per_day"], int(elapsed_minutes // 30))

    def so_far_summary(cycle_id, now_pos, slots_per_day, week_number=None):
        """Plan vs Actual as of right now.
        now_pos is a position in the cycle's timeline:
        ((week-1)*7 + day)*slots_per_day + elapsed_slots_today.
        Slots with position < now_pos are in the past; >= now_pos remain.
        week_number=None covers the whole 4-week cycle, otherwise one week.
        Columns: target (the week's total plan, or the cycle's 4-week target),
        actual so far, remaining planned (plan from now to end of week/cycle),
        variance = target - actual - remaining planned (hours the current
        plan still doesn't cover; negative = on track to exceed target)."""
        db = dbmod.get_db()
        items = cycle_items(cycle_id)
        pos_expr = "((week_number-1)*7 + day_of_week)*? + slot_index"
        wk_clause = " AND week_number=?" if week_number else ""
        wk_args = (week_number,) if week_number else ()
        rows = []
        for it in items:
            base = (cycle_id, it["id"]) + wk_args
            remaining = db.execute(
                "SELECT COUNT(*) FROM cycle_plan_slot WHERE cycle_id=? AND cycle_item_id=?"
                + wk_clause + " AND " + pos_expr + " >= ?",
                base + (slots_per_day, now_pos),
            ).fetchone()[0] * 0.5
            actual = db.execute(
                "SELECT COUNT(*) FROM actual_entry WHERE cycle_id=? AND cycle_item_id=?"
                + wk_clause + " AND " + pos_expr + " < ?",
                base + (slots_per_day, now_pos),
            ).fetchone()[0] * 0.5
            if week_number:
                target = db.execute(
                    "SELECT COUNT(*) FROM cycle_plan_slot WHERE cycle_id=? AND cycle_item_id=?"
                    + wk_clause, base,
                ).fetchone()[0] * 0.5
            else:
                target = it["target_hours_per_week"] * 4
            if target == 0 and actual == 0 and remaining == 0:
                continue
            rows.append({
                "id": it["id"], "description": it["description"],
                "category": it["category_name"], "color": it["category_color"],
                "priority": it["priority"],
                "target": target, "actual": actual, "remaining": remaining,
                "variance": round(target - actual - remaining, 2),
            })
        return rows

    PRIORITY_RANK = {"High": 0, "Medium": 1, "Low": 2}
    SORT_KEYS = {
        "description": lambda r: (r["description"] or "").lower(),
        "category": lambda r: (r["category"] or "").lower(),
        "priority": lambda r: PRIORITY_RANK.get(r["priority"], 99),
        "target": lambda r: r.get("target", 0),
        "planned": lambda r: r.get("planned", 0),
        "remaining": lambda r: r.get("remaining", 0),
        "actual": lambda r: r["actual"],
        "variance": lambda r: r["variance"],
    }

    def sort_rows(rows, table):
        """Sort rows by the ?<table>sort=&<table>dir= query params."""
        col = request.args.get(table + "sort")
        if rows and col in SORT_KEYS:
            reverse = request.args.get(table + "dir") == "desc"
            rows = sorted(rows, key=SORT_KEYS[col], reverse=reverse)
        return rows

    @app.context_processor
    def table_sort_helpers():
        def sort_link(table, col):
            args = request.args.to_dict()
            cur, d = args.get(table + "sort"), args.get(table + "dir", "asc")
            args[table + "sort"] = col
            args[table + "dir"] = "desc" if (cur == col and d == "asc") else "asc"
            return url_for(request.endpoint, **args)

        def arrow(table, col):
            if request.args.get(table + "sort") != col:
                return ""
            return " \u2191" if request.args.get(table + "dir", "asc") == "asc" else " \u2193"
        return {"sort_link": sort_link, "arrow": arrow}

    # ---------------- auth routes ----------------
    @app.route("/setup", methods=["GET", "POST"])
    def setup():
        db = dbmod.get_db()
        existing = db.execute("SELECT COUNT(*) FROM user").fetchone()[0]
        if existing > 0:
            return redirect(url_for("login"))
        if request.method == "POST":
            username = request.form["username"].strip()
            password = request.form["password"]
            db.execute(
                "INSERT INTO user (username, password_hash) VALUES (?,?)",
                (username, generate_password_hash(password)),
            )
            db.commit()
            flash("Account created. Please log in.")
            return redirect(url_for("login"))
        return render_template("setup.html")

    @app.route("/login", methods=["GET", "POST"])
    def login():
        db = dbmod.get_db()
        if db.execute("SELECT COUNT(*) FROM user").fetchone()[0] == 0:
            return redirect(url_for("setup"))
        if request.method == "POST":
            username = request.form["username"].strip()
            password = request.form["password"]
            user = db.execute("SELECT * FROM user WHERE username=?", (username,)).fetchone()
            if user and check_password_hash(user["password_hash"], password):
                session.permanent = True
                session["user_id"] = user["id"]
                return redirect(request.args.get("next") or url_for("dashboard"))
            flash("Invalid username or password.")
        return render_template("login.html")

    @app.route("/logout")
    def logout():
        session.clear()
        return redirect(url_for("login"))

    # ---------------- dashboard ----------------
    def category_chart_data(cycle_id):
        db = dbmod.get_db()
        categories = db.execute("SELECT name, color_hex FROM category ORDER BY sort_order").fetchall()
        by_week = {}
        for w in [1, 2, 3, 4]:
            rows = db.execute(
                "SELECT ci.category_name as name, COUNT(*) as cnt "
                "FROM actual_entry ae JOIN cycle_item ci ON ci.id = ae.cycle_item_id "
                "WHERE ae.cycle_id=? AND ae.week_number=? GROUP BY ci.category_name",
                (cycle_id, w),
            ).fetchall()
            by_week[w] = {r["name"]: r["cnt"] * 0.5 for r in rows}
        return {
            "categories": [{"name": c["name"], "color": c["color_hex"]} for c in categories],
            "by_week": by_week,
        }

    @app.route("/")
    @login_required
    def dashboard():
        cycle = get_active_cycle()
        if not cycle:
            return redirect(url_for("cycles"))
        current_week = request.args.get("week", type=int) or 1
        current_week = max(1, min(4, current_week))
        summary = week_summary(cycle["id"], current_week)
        totals = total_summary(cycle["id"])
        db = dbmod.get_db()
        items = db.execute(
            "SELECT id, description FROM cycle_item WHERE cycle_id=? ORDER BY description",
            (cycle["id"],),
        ).fetchall()
        week_ranges = {}
        for w in [1, 2, 3, 4]:
            d0 = cycle_date(cycle, w, 0)
            d6 = cycle_date(cycle, w, 6)
            week_ranges[w] = f"{d0.strftime('%-m/%-d')}-{d6.strftime('%-m/%-d')}" if d0 else None

        settings = get_settings()
        today = date.fromisoformat(central_today_iso())
        guess = today_week_day(cycle, today)
        so_far = so_far_cycle = None
        so_far_label = so_far_cycle_label = None
        if guess:
            sf_week, sf_day = guess
            S = settings["slots_per_day"]
            elapsed = slots_elapsed_today(settings)
            now_pos = ((sf_week - 1) * 7 + sf_day) * S + elapsed
            now_text = (cycle_date(cycle, sf_week, sf_day).strftime('%A %-m/%-d')
                        + " " + slot_time_label(elapsed, settings['plan_start_hour']))
            so_far = sort_rows(so_far_summary(cycle["id"], now_pos, S, sf_week), "f")
            so_far_label = f"Week {sf_week}: start of week through {now_text} (actual); {now_text} through end of Week {sf_week} (remaining planned)"
            so_far_cycle = sort_rows(so_far_summary(cycle["id"], now_pos, S), "c")
            so_far_cycle_label = f"Cycle: start through {now_text} (actual); {now_text} through end of Week 4 (remaining planned)"

        summary = sort_rows(summary, "w")
        totals = sort_rows(totals, "t")
        chart_data = category_chart_data(cycle["id"])
        note_row = db.execute(
            "SELECT note FROM week_note WHERE cycle_id=? AND week_number=?",
            (cycle["id"], current_week),
        ).fetchone()
        week_note = note_row["note"] if note_row else ""

        return render_template(
            "dashboard.html", cycle=cycle, week=current_week, summary=summary,
            totals=totals, items=items, week_ranges=week_ranges,
            so_far=so_far, so_far_label=so_far_label,
            so_far_cycle=so_far_cycle, so_far_cycle_label=so_far_cycle_label,
            chart_data=chart_data, week_note=week_note,
        )

    @app.route("/cycles/<int:cycle_id>/week/<int:week_number>/note", methods=["POST"])
    @login_required
    def save_week_note(cycle_id, week_number):
        db = dbmod.get_db()
        data = request.get_json(force=True, silent=True) or {}
        db.execute(
            "INSERT INTO week_note (cycle_id, week_number, note) VALUES (?,?,?) "
            "ON CONFLICT(cycle_id, week_number) DO UPDATE SET note=excluded.note",
            (cycle_id, week_number, data.get("note", "")),
        )
        db.commit()
        return jsonify({"ok": True})

    # ---------------- quick log ----------------
    @app.route("/log", methods=["GET", "POST"])
    @login_required
    def log_actual():
        cycle = get_active_cycle()
        if not cycle:
            flash("Start a cycle first.")
            return redirect(url_for("cycles"))
        settings = get_settings()
        db = dbmod.get_db()
        items = db.execute(
            "SELECT id, description FROM cycle_item WHERE cycle_id=? ORDER BY description",
            (cycle["id"],),
        ).fetchall()

        if request.method == "POST":
            week_number = int(request.form["week_number"])
            day_of_week = int(request.form["day_of_week"])
            slot_index = int(request.form["slot_index"])
            cycle_item_id = request.form.get("cycle_item_id") or None
            db.execute(
                "INSERT OR REPLACE INTO actual_entry "
                "(cycle_id, week_number, day_of_week, slot_index, cycle_item_id, logged_at) "
                "VALUES (?,?,?,?,?,?)",
                (cycle["id"], week_number, day_of_week, slot_index, cycle_item_id,
                 datetime.now(timezone.utc).isoformat()),
            )
            db.commit()
            flash("Logged.")
            return redirect(url_for("log_actual", week=week_number, day=day_of_week))

        week_number = request.args.get("week", type=int)
        day_of_week = request.args.get("day", type=int)
        if week_number is None or day_of_week is None:
            guess = today_week_day(cycle)
            if guess:
                week_number = week_number or guess[0]
                day_of_week = day_of_week if day_of_week is not None else guess[1]
            else:
                week_number = week_number or 1
                day_of_week = day_of_week if day_of_week is not None else datetime.now().weekday()

        n_slots = settings["slots_per_day"]
        slots = []
        for i in range(n_slots):
            existing = db.execute(
                "SELECT ae.cycle_item_id, ci.description FROM actual_entry ae "
                "LEFT JOIN cycle_item ci ON ci.id = ae.cycle_item_id "
                "WHERE ae.cycle_id=? AND ae.week_number=? AND ae.day_of_week=? AND ae.slot_index=?",
                (cycle["id"], week_number, day_of_week, i),
            ).fetchone()
            planned = db.execute(
                "SELECT cps.cycle_item_id, ci.description FROM cycle_plan_slot cps "
                "LEFT JOIN cycle_item ci ON ci.id = cps.cycle_item_id "
                "WHERE cps.cycle_id=? AND cps.week_number=? AND cps.day_of_week=? AND cps.slot_index=?",
                (cycle["id"], week_number, day_of_week, i),
            ).fetchone()
            slots.append({
                "index": i,
                "label": slot_time_label(i, settings["plan_start_hour"]),
                "actual_item_id": existing["cycle_item_id"] if existing else None,
                "actual_desc": existing["description"] if existing else None,
                "planned_item_id": planned["cycle_item_id"] if planned else None,
                "planned_desc": planned["description"] if planned else None,
            })

        day_dates = [cycle_date(cycle, week_number, d) for d in range(7)]
        day_labels = [
            f"{DAYS[d]} ({day_dates[d].strftime('%-m/%-d')})" if day_dates[d] else DAYS[d]
            for d in range(7)
        ]

        return render_template(
            "log.html", cycle=cycle, items=items, slots=slots,
            week_number=week_number, day_of_week=day_of_week, days=DAYS,
            day_labels=day_labels,
        )

    # ---------------- cycles ----------------
    @app.route("/cycles")
    @login_required
    def cycles():
        db = dbmod.get_db()
        all_cycles = db.execute("SELECT * FROM cycle ORDER BY id DESC").fetchall()
        ranges = {}
        for c in all_cycles:
            if c["start_date"]:
                d0 = cycle_date(c, 1, 0)
                d3 = cycle_date(c, 4, 6)
                ranges[c["id"]] = f"{d0.strftime('%-m/%-d')} - {d3.strftime('%-m/%-d')}"
        return render_template("cycles.html", cycles=all_cycles, ranges=ranges)

    @app.route("/cycles/new", methods=["GET", "POST"])
    @login_required
    def new_cycle():
        db = dbmod.get_db()
        if request.method == "POST":
            label = request.form["label"].strip()
            db.execute("UPDATE cycle SET is_active=0")
            cur = db.execute(
                "INSERT INTO cycle (label, start_date, created_at, is_active) VALUES (?,?,?,1)",
                (label, request.form.get("start_date") or None, datetime.now(timezone.utc).isoformat()),
            )
            cycle_id = cur.lastrowid

            # Freeze current items into cycle_item
            items = db.execute(
                "SELECT i.*, c.name as category_name, c.color_hex as category_color "
                "FROM item i LEFT JOIN category c ON c.id = i.category_id WHERE i.active=1"
            ).fetchall()
            item_to_cycle_item = {}
            for it in items:
                cur2 = db.execute(
                    "INSERT INTO cycle_item (cycle_id, source_item_id, description, category_name, "
                    "category_color, priority, target_hours_per_week) VALUES (?,?,?,?,?,?,?)",
                    (cycle_id, it["id"], it["description"], it["category_name"],
                     it["category_color"], it["priority"], it["weekly_target_hours"]),
                )
                item_to_cycle_item[it["id"]] = cur2.lastrowid

            # Mirror the live weekly plan into all 4 weeks
            plan_rows = db.execute("SELECT * FROM plan_slot").fetchall()
            for week_number in range(1, 5):
                for ps in plan_rows:
                    if ps["item_id"] not in item_to_cycle_item:
                        continue
                    db.execute(
                        "INSERT OR REPLACE INTO cycle_plan_slot "
                        "(cycle_id, week_number, day_of_week, slot_index, cycle_item_id) "
                        "VALUES (?,?,?,?,?)",
                        (cycle_id, week_number, ps["day_of_week"], ps["slot_index"],
                         item_to_cycle_item[ps["item_id"]]),
                    )
            db.commit()
            flash(f"Cycle '{label}' created from your current plan.")
            return redirect(url_for("dashboard"))
        return render_template("new_cycle.html", today=date.today().isoformat())

    @app.route("/cycles/<int:cycle_id>/activate", methods=["POST"])
    @login_required
    def activate_cycle(cycle_id):
        db = dbmod.get_db()
        db.execute("UPDATE cycle SET is_active=0")
        db.execute("UPDATE cycle SET is_active=1 WHERE id=?", (cycle_id,))
        db.commit()
        return redirect(url_for("dashboard"))

    @app.route("/cycles/<int:cycle_id>/set_start_date", methods=["POST"])
    @login_required
    def set_start_date(cycle_id):
        db = dbmod.get_db()
        db.execute(
            "UPDATE cycle SET start_date=? WHERE id=?",
            (request.form.get("start_date") or None, cycle_id),
        )
        db.commit()
        flash("Start date updated.")
        return redirect(url_for("cycles"))

    # ---------------- items (Section 1 equivalent) ----------------
    @app.route("/items", methods=["GET", "POST"])
    @login_required
    def items_view():
        db = dbmod.get_db()
        if request.method == "POST":
            description = request.form["description"].strip()
            category_id = request.form.get("category_id") or None
            priority = request.form.get("priority", "Medium")
            target = float(request.form.get("weekly_target_hours") or 0)
            db.execute(
                "INSERT INTO item (description, category_id, priority, weekly_target_hours) "
                "VALUES (?,?,?,?)",
                (description, category_id, priority, target),
            )
            db.commit()
            return redirect(url_for("items_view"))

        items = db.execute(
            "SELECT i.*, c.name as category_name, c.color_hex as category_color "
            "FROM item i LEFT JOIN category c ON c.id = i.category_id "
            "WHERE i.active=1"
        ).fetchall()
        items = [dict(it) for it in items]

        sort = request.args.get("sort", "default")
        direction = request.args.get("dir", "asc")
        priority_rank = {"High": 0, "Medium": 1, "Low": 2}
        sort_keys = {
            "description": lambda it: (it["description"] or "").lower(),
            "category": lambda it: (it["category_name"] or "").lower(),
            "priority": lambda it: priority_rank.get(it["priority"], 99),
            "hours": lambda it: it["weekly_target_hours"] or 0,
        }
        if sort in sort_keys:
            items.sort(key=sort_keys[sort], reverse=(direction == "desc"))
        else:
            items.sort(key=lambda it: (it["sort_order"], (it["description"] or "").lower()))

        def next_dir(col):
            return "desc" if (sort == col and direction == "asc") else "asc"

        def arrow(col):
            if sort != col:
                return ""
            return " \u2191" if direction == "asc" else " \u2193"

        categories = db.execute("SELECT * FROM category ORDER BY sort_order").fetchall()
        return render_template(
            "items.html", items=items, categories=categories,
            sort=sort, direction=direction, next_dir=next_dir, arrow=arrow,
        )

    @app.route("/items/<int:item_id>/update_target", methods=["POST"])
    @login_required
    def update_item_target(item_id):
        db = dbmod.get_db()
        data = request.get_json(force=True)
        target = float(data["weekly_target_hours"])
        db.execute("UPDATE item SET weekly_target_hours=? WHERE id=?", (target, item_id))
        db.commit()
        return jsonify({"ok": True})

    @app.route("/items/<int:item_id>/delete", methods=["POST"])
    @login_required
    def delete_item(item_id):
        db = dbmod.get_db()
        db.execute("UPDATE item SET active=0 WHERE id=?", (item_id,))
        db.commit()
        return redirect(url_for("items_view"))

    # ---------------- live weekly plan (Section 2 equivalent) ----------------
    @app.route("/plan", methods=["GET", "POST"])
    @login_required
    def plan_view():
        db = dbmod.get_db()
        settings = get_settings()
        if request.method == "POST":
            day_of_week = int(request.form["day_of_week"])
            slot_index = int(request.form["slot_index"])
            item_id = request.form.get("item_id") or None
            if item_id:
                db.execute(
                    "INSERT OR REPLACE INTO plan_slot (day_of_week, slot_index, item_id) VALUES (?,?,?)",
                    (day_of_week, slot_index, item_id),
                )
            else:
                db.execute(
                    "DELETE FROM plan_slot WHERE day_of_week=? AND slot_index=?",
                    (day_of_week, slot_index),
                )
            db.commit()
            return jsonify({"ok": True})

        items = db.execute(
            "SELECT id, description FROM item WHERE active=1 ORDER BY description"
        ).fetchall()
        n_slots = settings["slots_per_day"]
        grid = {}
        for row in db.execute("SELECT * FROM plan_slot"):
            grid[(row["day_of_week"], row["slot_index"])] = row["item_id"]
        slot_labels = [slot_time_label(i, settings["plan_start_hour"]) for i in range(n_slots)]
        return render_template(
            "plan.html", items=items, days=DAYS, n_slots=n_slots,
            slot_labels=slot_labels, grid=grid,
        )

    # ---------------- week plan override (per-cycle) ----------------
    @app.route("/cycles/<int:cycle_id>/week/<int:week_number>/plan", methods=["GET", "POST"])
    @login_required
    def week_plan_view(cycle_id, week_number):
        db = dbmod.get_db()
        settings = get_settings()
        items = cycle_items(cycle_id)
        if request.method == "POST":
            day_of_week = int(request.form["day_of_week"])
            slot_index = int(request.form["slot_index"])
            cycle_item_id = request.form.get("cycle_item_id") or None
            if cycle_item_id:
                db.execute(
                    "INSERT OR REPLACE INTO cycle_plan_slot "
                    "(cycle_id, week_number, day_of_week, slot_index, cycle_item_id) VALUES (?,?,?,?,?)",
                    (cycle_id, week_number, day_of_week, slot_index, cycle_item_id),
                )
            else:
                db.execute(
                    "DELETE FROM cycle_plan_slot WHERE cycle_id=? AND week_number=? "
                    "AND day_of_week=? AND slot_index=?",
                    (cycle_id, week_number, day_of_week, slot_index),
                )
            db.commit()
            return jsonify({"ok": True})

        n_slots = settings["slots_per_day"]
        grid = {}
        item_desc = {it["id"]: it["description"] for it in items}
        for row in db.execute(
            "SELECT * FROM cycle_plan_slot WHERE cycle_id=? AND week_number=?",
            (cycle_id, week_number),
        ):
            grid[(row["day_of_week"], row["slot_index"])] = {
                "id": row["cycle_item_id"],
                "desc": item_desc.get(row["cycle_item_id"], ""),
            }
        slot_labels = [slot_time_label(i, settings["plan_start_hour"]) for i in range(n_slots)]

        cycle = db.execute("SELECT * FROM cycle WHERE id=?", (cycle_id,)).fetchone()
        day_dates = [cycle_date(cycle, week_number, d) if cycle else None for d in range(7)]
        day_labels = [
            f"{DAYS[d]} ({day_dates[d].strftime('%-m/%-d')})" if day_dates[d] else DAYS[d]
            for d in range(7)
        ]

        return render_template(
            "week_plan.html", items=items, days=day_labels, n_slots=n_slots,
            slot_labels=slot_labels, grid=grid, cycle_id=cycle_id, week_number=week_number,
        )

    # ---------------- morning routine checklist ----------------
    def get_routine_state():
        """Load today's routine state, auto-resetting checks if the stored
        date has rolled over to a new day (Central Time)."""
        db = dbmod.get_db()
        today = central_today_iso()
        row = db.execute("SELECT * FROM routine_state WHERE id=1").fetchone()
        if row["date"] != today:
            db.execute(
                "UPDATE routine_state SET date=?, checks_json='{}' WHERE id=1",
                (today,),
            )
            db.commit()
            row = db.execute("SELECT * FROM routine_state WHERE id=1").fetchone()
        return row

    @app.route("/routine")
    @login_required
    def routine_view():
        return render_template("routine.html")

    @app.route("/api/routine/state")
    @login_required
    def routine_state_api():
        row = get_routine_state()
        return jsonify({
            "date": row["date"],
            "selected_column": row["selected_column"],
            "checks": json.loads(row["checks_json"]),
        })

    @app.route("/api/routine/check", methods=["POST"])
    @login_required
    def routine_check_api():
        row = get_routine_state()
        data = request.get_json(force=True)
        column = data["column"]
        index = int(data["index"])
        checked = bool(data["checked"])
        checks = json.loads(row["checks_json"])
        col_list = set(checks.get(column, []))
        if checked:
            col_list.add(index)
        else:
            col_list.discard(index)
        checks[column] = sorted(col_list)
        db = dbmod.get_db()
        db.execute("UPDATE routine_state SET checks_json=? WHERE id=1", (json.dumps(checks),))
        db.commit()
        return jsonify({"ok": True})

    @app.route("/api/routine/column", methods=["POST"])
    @login_required
    def routine_column_api():
        get_routine_state()  # ensure reset-if-stale happens first
        data = request.get_json(force=True)
        db = dbmod.get_db()
        db.execute("UPDATE routine_state SET selected_column=? WHERE id=1", (data["column"],))
        db.commit()
        return jsonify({"ok": True})

    return app


if __name__ == "__main__":
    app = create_app()
    app.run(debug=True, host="127.0.0.1", port=5000)
