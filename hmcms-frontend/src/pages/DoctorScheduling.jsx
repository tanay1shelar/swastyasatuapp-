export default function DoctorScheduling() {
    return (
        <main className="p-4" id="page-content">
            <h4 className="mb-4">Doctor Scheduling</h4>

            <div className="mb-4 shadow-sm text-center" style={{ animation: "revealItem 0.5s ease forwards", opacity: 0, background: "var(--content-bg, #ffffff)", borderRadius: "16px", padding: "6px", border: "1px solid var(--muted-border, #e2e8f0)" }}>
                <img src="/assets/images/scheduling-banner.png" className="w-100" style={{ height: "auto", maxHeight: "320px", objectFit: "contain", borderRadius: "12px", display: "inline-block" }} alt="Scheduling Banner" />
            </div>

            <div className="content-card p-4 mb-4">
                <h6 className="mb-3">Assign Doctor to Medical Camp</h6>
                <form id="doctorAssignmentForm" data-api-endpoint="/Healthcare%20&%20Medical%20Camp%20Management%20System/Healthcare-Medical-Camp-Management-System/api/assignments.php" className="row g-3">
                    <div className="col-md-3">
                        <label className="form-label text-muted small">Select Camp</label>
                        <select name="camp_id" className="form-select" required defaultValue="">
                            <option value="" disabled>Choose a camp...</option>
                            <option value="1">City Care Camp - Pune</option>
                            <option value="2">Rural Health Camp - Shirur</option>
                        </select>
                    </div>
                    <div className="col-md-3">
                        <label className="form-label text-muted small">Select Doctor</label>
                        <select name="doctor_id" className="form-select" required defaultValue="">
                            <option value="" disabled>Choose a doctor...</option>
                            <option value="D101">Dr. Rahul Verma (General)</option>
                            <option value="D102">Dr. Priya Sharma (Pediatrics)</option>
                        </select>
                    </div>
                    <div className="col-md-3">
                        <label className="form-label text-muted small">Date</label>
                        <input type="date" name="assignment_date" className="form-control" required />
                    </div>
                    <div className="col-md-3 d-flex align-items-end">
                        <button type="submit" className="btn btn-primary w-100">Assign to Camp</button>
                    </div>
                </form>
            </div>

            <div className="content-card p-4">
                <div className="d-flex justify-content-between align-items-center mb-3">
                    <h6>Current Assignments</h6>
                    <input type="text" className="form-control w-25" id="doctorSearch" placeholder="Filter assignments..." />
                </div>
                <table className="table table-hover border" id="scheduleTable">
                    <thead className="table-light">
                        <tr>
                            <th>Doctor Name</th>
                            <th>Specialization</th>
                            <th>Assigned Camp</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Dr. Rahul Verma</td>
                            <td>General Medicine</td>
                            <td>City Care Camp - Pune</td>
                            <td>15 Jul 2026</td>
                            <td><button className="btn btn-sm btn-outline-danger"><i className="fa-solid fa-trash"></i></button></td>
                        </tr>
                        <tr>
                            <td>Dr. Priya Sharma</td>
                            <td>Pediatrics</td>
                            <td>Rural Health Camp - Shirur</td>
                            <td>18 Jul 2026</td>
                            <td><button className="btn btn-sm btn-outline-danger"><i className="fa-solid fa-trash"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    );
}
