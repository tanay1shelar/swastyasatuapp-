export default function HealthWorkerAllocation() {
    return (
        <main className="p-4" id="page-content">
            <h4 className="mb-4">Health Worker Allocation</h4>

            <div className="mb-4 shadow-sm text-center" style={{ animation: "revealItem 0.5s ease forwards", opacity: 0, background: "var(--content-bg, #ffffff)", borderRadius: "16px", padding: "6px", border: "1px solid var(--muted-border, #e2e8f0)" }}>
                <img src="/assets/images/allocation-banner.png" className="w-100" style={{ height: "auto", maxHeight: "320px", objectFit: "contain", borderRadius: "12px", display: "inline-block" }} alt="Allocation Banner" />
            </div>

            <div className="content-card p-4 mb-4">
                <h6 className="mb-3">Allocate Health Worker to Camp</h6>
                <form id="workerAllocationForm" data-api-endpoint="/Healthcare%20&%20Medical%20Camp%20Management%20System/Healthcare-Medical-Camp-Management-System/api/allocations.php" className="row g-3">
                    <div className="col-md-3">
                        <label className="form-label text-muted small">Select Camp</label>
                        <select name="camp_id" className="form-select" required defaultValue="">
                            <option value="" disabled>Choose a camp...</option>
                            <option value="1">City Care Camp - Pune</option>
                            <option value="2">Rural Health Camp - Shirur</option>
                        </select>
                    </div>
                    <div className="col-md-3">
                        <label className="form-label text-muted small">Select Worker</label>
                        <select name="worker_id" className="form-select" required defaultValue="">
                            <option value="" disabled>Choose a worker...</option>
                            <option value="W201">Sunita Patel (Nurse)</option>
                            <option value="W202">Ramesh Kumar (Lab Tech)</option>
                        </select>
                    </div>
                    <div className="col-md-3">
                        <label className="form-label text-muted small">Shift</label>
                        <select name="shift" className="form-select" required defaultValue="Morning">
                            <option value="Morning">Morning (8 AM - 2 PM)</option>
                            <option value="Evening">Evening (2 PM - 8 PM)</option>
                        </select>
                    </div>
                    <div className="col-md-3 d-flex align-items-end">
                        <button type="submit" className="btn btn-primary w-100">Allocate Worker</button>
                    </div>
                </form>
            </div>

            <div className="content-card p-4">
                <div className="d-flex justify-content-between align-items-center mb-3">
                    <h6>Current Allocations</h6>
                    <input type="text" className="form-control w-25" id="workerSearch" placeholder="Filter allocations..." />
                </div>
                <table className="table table-hover border" id="allocationTable">
                    <thead className="table-light">
                        <tr>
                            <th>Worker Name</th>
                            <th>Role</th>
                            <th>Assigned Camp</th>
                            <th>Shift</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Sunita Patel</td>
                            <td>Nurse</td>
                            <td>City Care Camp - Pune</td>
                            <td>Morning</td>
                            <td><button className="btn btn-sm btn-outline-danger"><i className="fa-solid fa-trash"></i></button></td>
                        </tr>
                        <tr>
                            <td>Ramesh Kumar</td>
                            <td>Lab Tech</td>
                            <td>Rural Health Camp - Shirur</td>
                            <td>Evening</td>
                            <td><button className="btn btn-sm btn-outline-danger"><i className="fa-solid fa-trash"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    );
}
