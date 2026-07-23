import { useState, useEffect } from 'react';

export default function MedicineInventory() {
    const [medicines, setMedicines] = useState([]);
    const [search, setSearch] = useState('');
    const [loading, setLoading] = useState(true);
    
    // Add Medicine Form state
    const [addForm, setAddForm] = useState({
        med_name: '',
        category: 'Tablet',
        batch_no: '',
        expiry_date: '',
        stock_qty: ''
    });

    // Update Stock Form state
    const [selectedMedId, setSelectedMedId] = useState(null);
    const [stockForm, setStockForm] = useState({
        update_type: 'add',
        update_qty: ''
    });

    const fetchMedicines = async () => {
        setLoading(true);
        try {
            const res = await fetch('/api/medicines.php');
            const data = await res.json();
            if (data.success && Array.isArray(data.medicines)) {
                setMedicines(data.medicines);
            }
        } catch (err) {
            console.error('Error fetching medicines:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchMedicines();
    }, []);

    const handleAddSubmit = async (e) => {
        e.preventDefault();
        try {
            const res = await fetch('/api/medicines.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(addForm)
            });
            const data = await res.json();
            if (data.success) {
                if (window.showToast) window.showToast('Medicine added successfully!', 'success');
                setAddForm({ med_name: '', category: 'Tablet', batch_no: '', expiry_date: '', stock_qty: '' });
                const closeBtn = document.getElementById('closeAddModal');
                if (closeBtn) closeBtn.click();
                fetchMedicines();
            } else {
                alert(data.error || 'Failed to add medicine.');
            }
        } catch (err) {
            console.error(err);
            alert('A network error occurred.');
        }
    };

    const handleStockSubmit = async (e) => {
        e.preventDefault();
        if (!selectedMedId) return;
        try {
            const res = await fetch('/api/stock.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    medicine_id: selectedMedId,
                    update_type: stockForm.update_type,
                    update_qty: stockForm.update_qty
                })
            });
            const data = await res.json();
            if (data.success) {
                if (window.showToast) window.showToast('Stock updated successfully!', 'success');
                setStockForm({ update_type: 'add', update_qty: '' });
                const closeBtn = document.getElementById('closeStockModal');
                if (closeBtn) closeBtn.click();
                fetchMedicines();
            } else {
                alert(data.error || 'Failed to update stock.');
            }
        } catch (err) {
            console.error(err);
            alert('A network error occurred.');
        }
    };

    const filteredMedicines = medicines.filter(m => 
        (m.name || '').toLowerCase().includes(search.toLowerCase()) ||
        (m.category || '').toLowerCase().includes(search.toLowerCase()) ||
        (m.batch_no || '').toLowerCase().includes(search.toLowerCase())
    );

    return (
        <main className="p-4" id="page-content">
            <h4 className="mb-4 gradient-heading"><i className="fa-solid fa-pills me-2 text-accent"></i> Medicine Inventory</h4>

            <div className="mb-4 shadow-sm text-center" style={{ animation: "revealItem 0.5s ease forwards", opacity: 0, background: "var(--content-bg, #ffffff)", borderRadius: "16px", padding: "6px", border: "1px solid var(--muted-border, #e2e8f0)" }}>
                <img src="/assets/images/inventory-banner.png" className="w-100" style={{ height: "auto", maxHeight: "320px", objectFit: "contain", borderRadius: "12px", display: "inline-block" }} alt="Inventory Banner" />
            </div>

            <div className="content-card p-4 reveal-stagger">
                <div className="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h6 className="fw-bold mb-0">Current Stock Details</h6>
                    <div className="d-flex gap-2 align-items-center">
                        <input 
                            type="text" 
                            className="form-control w-auto" 
                            placeholder="Search medicine..." 
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                        <button className="btn btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addMedicineModalReact">
                            <i className="fa-solid fa-plus me-2"></i> Add New Medicine
                        </button>
                    </div>
                </div>
                
                <div className="table-responsive">
                    <table className="table table-hover border align-middle">
                        <thead className="table-light">
                            <tr>
                                <th>Sr.</th>
                                <th>Medicine Name</th>
                                <th>Category</th>
                                <th>Batch No.</th>
                                <th>Expiry Date</th>
                                <th>Current Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr><td colSpan="7" className="text-center py-4">Loading stock records...</td></tr>
                            ) : filteredMedicines.length === 0 ? (
                                <tr><td colSpan="7" className="text-center py-4 text-muted">No medicine records found.</td></tr>
                            ) : (
                                filteredMedicines.map((m, idx) => {
                                    const isLow = Number(m.stock_qty) < 100;
                                    return (
                                        <tr key={m.id || idx}>
                                            <td>{idx + 1}</td>
                                            <td className="fw-bold">{m.name}</td>
                                            <td><span className="badge bg-light text-dark border">{m.category}</span></td>
                                            <td><code>{m.batch_no}</code></td>
                                            <td>{m.expiry_date}</td>
                                            <td>
                                                <span className={`status-pill ${isLow ? 'status-pill-danger' : 'status-pill-success'}`}>
                                                    <i className={`fa-solid ${isLow ? 'fa-triangle-exclamation' : 'fa-circle-check'}`}></i> {m.stock_qty} Units
                                                </span>
                                            </td>
                                            <td>
                                                <button 
                                                    className="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#updateStockModalReact"
                                                    onClick={() => setSelectedMedId(m.id)}
                                                >
                                                    <i className="fa-solid fa-pen-to-square me-1"></i> Update Stock
                                                </button>
                                            </td>
                                        </tr>
                                    );
                                })
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* ADD MEDICINE MODAL */}
            <div className="modal fade" id="addMedicineModalReact" tabIndex="-1">
                <div className="modal-dialog">
                    <div className="modal-content rounded-4 border-0 shadow">
                        <div className="modal-header">
                            <h5 className="modal-title fw-bold"><i className="fa-solid fa-plus-circle me-2 text-primary"></i> Add New Medicine</h5>
                            <button type="button" className="btn-close" id="closeAddModal" data-bs-dismiss="modal"></button>
                        </div>
                        <form onSubmit={handleAddSubmit}>
                            <div className="modal-body">
                                <div className="mb-3">
                                    <label className="form-label small fw-bold">Medicine Name</label>
                                    <input 
                                        type="text" 
                                        className="form-control" 
                                        required 
                                        value={addForm.med_name}
                                        onChange={(e) => setAddForm({ ...addForm, med_name: e.target.value })}
                                        placeholder="e.g. Paracetamol 500mg"
                                    />
                                </div>
                                <div className="mb-3">
                                    <label className="form-label small fw-bold">Category</label>
                                    <select 
                                        className="form-select" 
                                        value={addForm.category}
                                        onChange={(e) => setAddForm({ ...addForm, category: e.target.value })}
                                    >
                                        <option value="Tablet">Tablet</option>
                                        <option value="Capsule">Capsule</option>
                                        <option value="Syrup">Syrup</option>
                                        <option value="Injection">Injection</option>
                                    </select>
                                </div>
                                <div className="mb-3">
                                    <label className="form-label small fw-bold">Batch No.</label>
                                    <input 
                                        type="text" 
                                        className="form-control" 
                                        required 
                                        value={addForm.batch_no}
                                        onChange={(e) => setAddForm({ ...addForm, batch_no: e.target.value })}
                                        placeholder="e.g. BCH-102"
                                    />
                                </div>
                                <div className="mb-3">
                                    <label className="form-label small fw-bold">Expiry Date</label>
                                    <input 
                                        type="date" 
                                        className="form-control" 
                                        required 
                                        value={addForm.expiry_date}
                                        onChange={(e) => setAddForm({ ...addForm, expiry_date: e.target.value })}
                                    />
                                </div>
                                <div className="mb-3">
                                    <label className="form-label small fw-bold">Initial Stock Quantity</label>
                                    <input 
                                        type="number" 
                                        className="form-control" 
                                        required 
                                        value={addForm.stock_qty}
                                        onChange={(e) => setAddForm({ ...addForm, stock_qty: e.target.value })}
                                        placeholder="e.g. 500"
                                    />
                                </div>
                            </div>
                            <div className="modal-footer">
                                <button type="button" className="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" className="btn btn-primary rounded-pill px-4">Save Medicine</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {/* UPDATE STOCK MODAL */}
            <div className="modal fade" id="updateStockModalReact" tabIndex="-1">
                <div className="modal-dialog">
                    <div className="modal-content rounded-4 border-0 shadow">
                        <div className="modal-header">
                            <h5 className="modal-title fw-bold"><i className="fa-solid fa-boxes-stacked me-2 text-primary"></i> Update Stock Quantity</h5>
                            <button type="button" className="btn-close" id="closeStockModal" data-bs-dismiss="modal"></button>
                        </div>
                        <form onSubmit={handleStockSubmit}>
                            <div className="modal-body">
                                <div className="mb-3">
                                    <label className="form-label small fw-bold">Action Type</label>
                                    <select 
                                        className="form-select"
                                        value={stockForm.update_type}
                                        onChange={(e) => setStockForm({ ...stockForm, update_type: e.target.value })}
                                    >
                                        <option value="add">Add to Stock (+)</option>
                                        <option value="remove">Remove from Stock (-)</option>
                                    </select>
                                </div>
                                <div className="mb-3">
                                    <label className="form-label small fw-bold">Quantity</label>
                                    <input 
                                        type="number" 
                                        className="form-control" 
                                        min="1"
                                        required 
                                        value={stockForm.update_qty}
                                        onChange={(e) => setStockForm({ ...stockForm, update_qty: e.target.value })}
                                        placeholder="Enter quantity to add/remove"
                                    />
                                </div>
                            </div>
                            <div className="modal-footer">
                                <button type="button" className="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" className="btn btn-primary rounded-pill px-4">Update Stock</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    );
}
