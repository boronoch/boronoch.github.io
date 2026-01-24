from flask import Flask, render_template, request, redirect, url_for
import sqlite3

app = Flask(__name__)

# Database setup
def init_db():
    conn = sqlite3.connect('survey.db')
    c = conn.cursor()

    # Create topics table
    c.execute('''CREATE TABLE IF NOT EXISTS topics (
        idx INTEGER PRIMARY KEY,
        source TEXT,
        category TEXT,
        topic TEXT,
        description TEXT,
        done INTEGER DEFAULT 0
    )''')

    # Create survey responses table
    c.execute('''CREATE TABLE IF NOT EXISTS responses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        topic_id INTEGER,
        rank INTEGER,
        willing_to_lead INTEGER,
        comment TEXT,
        additional_comments TEXT,
        FOREIGN KEY (topic_id) REFERENCES topics(idx)
    )''')

    conn.commit()
    conn.close()

@app.route('/')
def home():
    return redirect(url_for('admin_page'))

# Admin page to select topics for survey
@app.route('/admin', methods=['GET', 'POST'])
def admin_page():
    conn = sqlite3.connect('survey.db')
    c = conn.cursor()

    if request.method == 'POST':
        selected_topics = request.form.getlist('topics')
        c.execute('UPDATE topics SET done = 0')
        c.executemany('UPDATE topics SET done = 1 WHERE idx = ?', [(t,) for t in selected_topics])
        conn.commit()

    c.execute('SELECT * FROM topics')
    topics = c.fetchall()
    conn.close()

    return render_template('admin.html', topics=topics)

# Survey form
@app.route('/survey', methods=['GET', 'POST'])
def survey():
    conn = sqlite3.connect('survey.db')
    c = conn.cursor()

    c.execute('SELECT * FROM topics WHERE done = 1')
    topics = c.fetchall()

    if request.method == 'POST':
        name = request.form['name']
        additional_comments = request.form['additional_comments']
        
        for topic in topics:
            topic_id = topic[0]
            rank = request.form.get(f'rank_{topic_id}', None)
            willing_to_lead = 1 if f'willing_to_lead_{topic_id}' in request.form else 0
            comment = request.form.get(f'comment_{topic_id}', '')

            c.execute('''INSERT INTO responses (name, topic_id, rank, willing_to_lead, comment, additional_comments)
                         VALUES (?, ?, ?, ?, ?, ?)''',
                      (name, topic_id, rank, willing_to_lead, comment, additional_comments))

        conn.commit()
        conn.close()
        return redirect(url_for('thank_you'))

    conn.close()
    return render_template('survey.html', topics=topics)

@app.route('/thank-you')
def thank_you():
    return "Thank you for completing the survey!"

# View survey results
@app.route('/results')
def results():
    conn = sqlite3.connect('survey.db')
    c = conn.cursor()

    c.execute('''SELECT t.topic, r.rank, r.willing_to_lead, r.comment, r.additional_comments, r.name
                 FROM responses r
                 JOIN topics t ON r.topic_id = t.idx
                 ORDER BY r.topic_id, r.rank''')

    results = c.fetchall()
    conn.close()

    return render_template('results.html', results=results)

if __name__ == '__main__':
    init_db()
    app.run(debug=True)
