import os
import sqlite3
from datetime import datetime
from flask import Flask, request, jsonify, g
from flask_cors import CORS

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATABASE = os.path.join(BASE_DIR, 'hmcms.db')

app = Flask(__name__)
CORS(app, resources={r"/api/*": {"origins": "*"}})

SIMPLE_TABLES = {
    'assignments': '''
        CREATE TABLE IF NOT EXISTS assignments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            camp_id TEXT NOT NULL,
            doctor_id TEXT NOT NULL,
            assignment_date TEXT NOT NULL,
            created_at TEXT NOT NULL
        )
    ''',
    'allocations': '''
        CREATE TABLE IF NOT EXISTS allocations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            camp_id TEXT NOT NULL,
            worker_id TEXT NOT NULL,
            shift_date TEXT NOT NULL,
            created_at TEXT NOT NULL
        )
    ''',
    'notifications': '''
        CREATE TABLE IF NOT EXISTS notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            audience TEXT NOT NULL,
            title TEXT NOT NULL,
            message TEXT NOT NULL,
            created_at TEXT NOT NULL
        )
    ''',
    'medicines': '''
        CREATE TABLE IF NOT EXISTS medicines (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            category TEXT NOT NULL,
            batch_no TEXT NOT NULL,
            expiry_date TEXT NOT NULL,
            stock_qty INTEGER NOT NULL,
            created_at TEXT NOT NULL
        )
    ''',
    'stock_updates': '''
        CREATE TABLE IF NOT EXISTS stock_updates (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            medicine_id INTEGER NOT NULL,
            update_type TEXT NOT NULL,
            update_qty INTEGER NOT NULL,
            created_at TEXT NOT NULL,
            FOREIGN KEY (medicine_id) REFERENCES medicines(id)
        )
    ''',
}

def get_db():
    
    db = getattr(g, '_database', None)
    if db is None:
        db = g._database = sqlite3.connect(DATABASE)
        db.row_factory = sqlite3.Row
    return db

def rows_to_list(rows):
    return [dict(row) for row in rows]

@app.teardown_appcontext
def close_connection(exception):
    db = getattr(g, '_database', None)
    if db is not None:
        db.close()

def init_db():
    conn = sqlite3.connect(DATABASE)
    cursor = conn.cursor()
    for sql in SIMPLE_TABLES.values():
        cursor.execute(sql)
    conn.commit()
    conn.close()

@app.route('/')
def home():
    return jsonify({'status': 'ok', 'message': 'HMCMS backend is running'})

@app.route('/api/assignments', methods=['GET'])
def list_assignments():
    db = get_db()
    rows = db.execute('SELECT * FROM assignments ORDER BY created_at DESC').fetchall()
    return jsonify({'success': True, 'assignments': rows_to_list(rows)})

@app.route('/api/assignments', methods=['POST'])
def create_assignment():
    data = request.get_json(force=True)
    camp_id = data.get('camp_id')
    doctor_id = data.get('doctor_id')
    assignment_date = data.get('assignment_date')
    if not camp_id or not doctor_id or not assignment_date:
        return jsonify({'success': False, 'error': 'camp_id, doctor_id and assignment_date are required'}), 400
    created_at = datetime.utcnow().isoformat()
    db = get_db()
    cursor = db.execute(
        'INSERT INTO assignments (camp_id, doctor_id, assignment_date, created_at) VALUES (?, ?, ?, ?)',
        (camp_id, doctor_id, assignment_date, created_at)
    )
    db.commit()
    return jsonify({'success': True, 'message': 'Assignment saved', 'id': cursor.lastrowid})

@app.route('/api/allocations', methods=['GET'])
def list_allocations():
    db = get_db()
    rows = db.execute('SELECT * FROM allocations ORDER BY created_at DESC').fetchall()
    return jsonify({'success': True, 'allocations': rows_to_list(rows)})

@app.route('/api/allocations', methods=['POST'])
def create_allocation():
    data = request.get_json(force=True)
    camp_id = data.get('camp_id')
    worker_id = data.get('worker_id')
    shift_date = data.get('shift_date')
    if not camp_id or not worker_id or not shift_date:
        return jsonify({'success': False, 'error': 'camp_id, worker_id and shift_date are required'}), 400
    created_at = datetime.utcnow().isoformat()
    db = get_db()
    cursor = db.execute(
        'INSERT INTO allocations (camp_id, worker_id, shift_date, created_at) VALUES (?, ?, ?, ?)',
        (camp_id, worker_id, shift_date, created_at)
    )
    db.commit()
    return jsonify({'success': True, 'message': 'Allocation saved', 'id': cursor.lastrowid})

@app.route('/api/notifications', methods=['GET'])
def list_notifications():
    db = get_db()
    rows = db.execute('SELECT * FROM notifications ORDER BY created_at DESC').fetchall()
    return jsonify({'success': True, 'notifications': rows_to_list(rows)})

@app.route('/api/notifications', methods=['POST'])
def create_notification():
    data = request.get_json(force=True)
    audience = data.get('audience')
    title = data.get('title')
    message = data.get('message')
    if not audience or not title or not message:
        return jsonify({'success': False, 'error': 'audience, title and message are required'}), 400
    created_at = datetime.utcnow().isoformat()
    db = get_db()
    cursor = db.execute(
        'INSERT INTO notifications (audience, title, message, created_at) VALUES (?, ?, ?, ?)',
        (audience, title, message, created_at)
    )
    db.commit()
    return jsonify({'success': True, 'message': 'Notification saved', 'id': cursor.lastrowid})

@app.route('/api/medicines', methods=['GET'])
def list_medicines():
    db = get_db()
    rows = db.execute('SELECT * FROM medicines ORDER BY created_at DESC').fetchall()
    return jsonify({'success': True, 'medicines': rows_to_list(rows)})

@app.route('/api/medicines', methods=['POST'])
def add_medicine():
    data = request.get_json(force=True)
    name = data.get('med_name')
    category = data.get('category')
    batch_no = data.get('batch_no')
    expiry_date = data.get('expiry_date')
    stock_qty = data.get('stock_qty')
    if not name or not category or not batch_no or not expiry_date or stock_qty is None:
        return jsonify({'success': False, 'error': 'All medicine fields are required'}), 400
    try:
        stock_qty = int(stock_qty)
    except ValueError:
        return jsonify({'success': False, 'error': 'stock_qty must be a number'}), 400
    created_at = datetime.utcnow().isoformat()
    db = get_db()
    cursor = db.execute(
        'INSERT INTO medicines (name, category, batch_no, expiry_date, stock_qty, created_at) VALUES (?, ?, ?, ?, ?, ?)',
        (name, category, batch_no, expiry_date, stock_qty, created_at)
    )
    db.commit()
    return jsonify({'success': True, 'message': 'Medicine saved', 'id': cursor.lastrowid})

@app.route('/api/stock', methods=['POST'])
def update_stock():
    data = request.get_json(force=True)
    medicine_id = data.get('medicine_id')
    update_type = data.get('update_type')
    update_qty = data.get('update_qty')
    if not medicine_id or not update_type or update_qty is None:
        return jsonify({'success': False, 'error': 'medicine_id, update_type and update_qty are required'}), 400
    try:
        medicine_id = int(medicine_id)
        update_qty = int(update_qty)
    except ValueError:
        return jsonify({'success': False, 'error': 'medicine_id and update_qty must be numbers'}), 400
    if update_type not in ('add', 'remove'):
        return jsonify({'success': False, 'error': 'update_type must be add or remove'}), 400
    db = get_db()
    current = db.execute('SELECT stock_qty FROM medicines WHERE id = ?', (medicine_id,)).fetchone()
    if current is None:
        return jsonify({'success': False, 'error': 'Medicine not found'}), 404
    new_stock = current['stock_qty'] + update_qty if update_type == 'add' else current['stock_qty'] - update_qty
    new_stock = max(new_stock, 0)
    db.execute('UPDATE medicines SET stock_qty = ? WHERE id = ?', (new_stock, medicine_id))
    db.execute(
        'INSERT INTO stock_updates (medicine_id, update_type, update_qty, created_at) VALUES (?, ?, ?, ?)',
        (medicine_id, update_type, update_qty, datetime.utcnow().isoformat())
    )
    db.commit()
    return jsonify({'success': True, 'message': 'Stock updated', 'medicine_id': medicine_id, 'stock_qty': new_stock})

if __name__ == '__main__':
    if not os.path.exists(DATABASE):
        init_db()
    app.run(host='0.0.0.0', port=5000, debug=True)
