import os
import json
from datetime import datetime, timezone, date, timedelta
from zoneinfo import ZoneInfo

from flask import Flask, render_template, request, redirect, url_for, session, flash, jsonify
from werkzeug.security import generate_password_hash, check_password_hash

from . import db as dbmod
from .db import DAYS, slot_time_label, cycle_date, today_week_day

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


def central_today_iso():
    return datetime.now(ZoneInfo("America/Chicago")).date().isoformat()


def create_app():
    app = Flask(__name__)
    app.config["DATABASE"] = os.environ.get(
        "TIMEBUDGET_DB", os.path.join(BASE_DIR, "timebudget.db")
    )
    app.config["SECRET_KEY"] = os.environ.get("TIMEBUDGET_SECRET", "dev-secret-change-me")

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

    def get_active_cycle():
        db = dbmod.get_db()
        return db.execute("SELECT * FROM cycle WHERE is_active=1 ORDER BY id DESC LIMIT 1").fetchone()

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
            target4 = it["target_hours_per_week"] * 4
            rows.append({
                "id": it["id"], "description": it["description"],
                "category": it["category_name"], "color": it["category_color"],
                "planned": target4, "actual": actual, "variance": round(target4 - actual, 2),
            })
        return rows

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
                session["user_id"] = user["id"]
                return redirect(request.args.get("next") or url_for("dashboard"))
            flash("Invalid username or password.")
        return render_template("login.html")

    @app.route("/logout")
    def logout():
        session.clear()
        return redirect(url_for("login"))

    # ---------------- dashboard ----------------
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
        return render_template(
            "dashboard.html", cycle=cycle, week=current_week, summary=summary,
            totals=totals, items=items, week_ranges=week_ranges,
        )

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
            "WHERE i.active=1 ORDER BY i.sort_order, i.description"
        ).fetchall()
        categories = db.execute("SELECT * FROM category ORDER BY sort_order").fetchall()
        return render_template("items.html", items=items, categories=categories)

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
        return render_template(
            "week_plan.html", items=items, days=DAYS, n_slots=n_slots,
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
