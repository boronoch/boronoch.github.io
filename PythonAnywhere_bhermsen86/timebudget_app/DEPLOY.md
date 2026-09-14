# Deploying to PythonAnywhere

## One thing to know upfront

PythonAnywhere's **free tier does not support custom domains** - your app will
live at `https://yourusername.pythonanywhere.com`, not under bryanhermsen.com.
Getting a bryanhermsen.com subdomain (e.g. `time.bryanhermsen.com`) requires
a paid PythonAnywhere plan (currently starting around $10/month). For a
personal tool only you use, the free `pythonanywhere.com` URL works exactly
the same - just bookmark it or add it to your phone's home screen. This guide
assumes the free tier; upgrading later doesn't require any code changes.

## 1. Create a PythonAnywhere account

Sign up at pythonanywhere.com (the free "Beginner" account is fine to start).

## 2. Upload the project files

In the PythonAnywhere dashboard, go to the **Files** tab and create a
directory, e.g. `timebudget`. Upload every file in this project there,
keeping the folder structure:

```
timebudget/
  wsgi.py
  requirements.txt
  migrate_from_excel.py
  timebudget.db          (already contains your Time Budget 2 + SEP 2026 data)
  app/
    __init__.py
    db.py
    schema.sql
    templates/
      *.html
```

The easiest way: zip the project locally, upload the zip via the Files tab,
then unzip it with a Bash console (see step 3).

## 3. Open a Bash console

From the **Consoles** tab, start a new Bash console. Navigate to your
project folder:

```bash
cd ~/timebudget
unzip timebudget_app.zip   # if you uploaded a zip
```

Flask is pre-installed on PythonAnywhere, so no pip install should be
needed. If you do get a `ModuleNotFoundError: No module named 'flask'`:

```bash
pip install --user flask
```

## 4. (Optional) Re-run the migration

Your uploaded `timebudget.db` already has your Time Budget 2 items and
SEP 2026 cycle data imported. If you want to re-import from a newer copy
of your spreadsheet instead, upload that `.xlsx` file and run:

```bash
python3 migrate_from_excel.py your_workbook.xlsx timebudget.db
```

This only adds new cycle tabs it doesn't already recognize - it's safe to
run again later as you add new dated cycle tabs to your spreadsheet, if you
want to keep both in sync manually.

## 5. Create the web app

Go to the **Web** tab -> **Add a new web app** -> choose **Manual
configuration** (not one of the framework quick-starts) -> pick the Python
version shown as available (3.10 or newer is fine).

## 6. Point it at this project

On the Web tab, under **Code**:
- **Source code**: `/home/yourusername/timebudget`
- **Working directory**: `/home/yourusername/timebudget`

Click the **WSGI configuration file** link and replace its contents with:

```python
import sys
path = '/home/yourusername/timebudget'
if path not in sys.path:
    sys.path.insert(0, path)

from wsgi import application
```

(Replace `yourusername` with your actual PythonAnywhere username in both
places above.)

## 7. Reload and set up your account

Click the green **Reload** button on the Web tab, then visit your app's
URL. The first visit takes you to `/setup` to create your login (username
and password) - only needs to be done once. After that, log in normally at
`/login`.

## 8. Day to day use

- **Log Time**: pick a week and day, tap the dropdown for each half-hour
  slot you want to fill in. Saves automatically as you pick.
- **Dashboard**: shows Plan vs Actual for whichever week you're viewing,
  plus the running 4-week Total.
- **Items**: add/remove things in your Time Inventory.
- **Weekly Plan**: edit your live recurring template.
- **Cycles**: start a new 4-week cycle here when ready - this freezes a
  snapshot of your current Items and Weekly Plan automatically, the same
  way you were doing by hand with Paste Special > Values in Excel, except
  now it happens every time without you having to remember.

## Keeping the app itself up to date

If you (or I) make further code changes, re-upload the changed files and
click **Reload** on the Web tab again - no need to redo steps 1-6.
