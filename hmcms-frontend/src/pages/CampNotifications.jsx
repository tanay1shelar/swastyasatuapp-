export default function CampNotifications() {
    return (
        <main className="p-4" id="page-content">
            <h4 className="mb-4">Camp Announcements & Notifications</h4>

            <div className="mb-4 shadow-sm text-center" style={{ animation: "revealItem 0.5s ease forwards", opacity: 0, background: "var(--content-bg, #ffffff)", borderRadius: "16px", padding: "6px", border: "1px solid var(--muted-border, #e2e8f0)" }}>
                <img src="/assets/images/medical-banner.png" className="w-100" style={{ height: "auto", maxHeight: "320px", objectFit: "contain", borderRadius: "12px", display: "inline-block" }} alt="Medical Camp Banner" />
            </div>

            <div className="row reveal-stagger">
                <div className="col-md-5">
                    <div className="content-card p-4">
                        <h6 className="mb-3">Create New Notification</h6>
                        <form id="notificationForm" data-api-endpoint="/Healthcare%20&%20Medical%20Camp%20Management%20System/Healthcare-Medical-Camp-Management-System/api/notifications.php">
                            <div className="mb-3">
                                <label className="form-label text-muted small">Target Audience</label>
                                <select name="audience" className="form-select">
                                    <option value="all">All Users (Doctors, Workers, Patients)</option>
                                    <option value="doctors">Doctors Only</option>
                                    <option value="workers">Health Workers Only</option>
                                </select>
                            </div>
                            <div className="mb-3">
                                <label className="form-label text-muted small">Subject/Title</label>
                                <input type="text" name="title" className="form-control" placeholder="e.g., Update on Shirur Camp" required />
                            </div>
                            <div className="mb-4">
                                <label className="form-label text-muted small">Message</label>
                                <textarea name="message" className="form-control" rows="4" placeholder="Type your announcement here..." required></textarea>
                            </div>
                            <button type="submit" className="btn btn-primary w-100"><i className="fa-solid fa-paper-plane me-2"></i> Publish Notification</button>
                        </form>
                    </div>
                </div>

                <div className="col-md-7">
                    <div className="content-card p-4 h-100">
                        <div className="d-flex justify-content-between align-items-center mb-4">
                            <h6 className="mb-0">Recent Announcements</h6>
                            <input type="text" className="form-control w-50" id="notificationSearch" placeholder="Search announcements..." />
                        </div>
                        
                        <div id="notificationList" className="reveal-stagger">
                            <div className="border-start border-4 border-primary ps-3 mb-4 notification-item">
                                <div className="d-flex justify-content-between">
                                    <h6 className="mb-1">Shift Timings Updated for Pune Camp</h6>
                                    <small className="text-muted">Today, 10:30 AM</small>
                                </div>
                                <p className="text-muted small mb-1">To: <strong>Doctors Only</strong></p>
                                <p className="mb-0 text-dark">Please note that the morning shift for the City Care Camp will now begin at 8:00 AM instead of 9:00 AM.</p>
                            </div>
                            <div className="border-start border-4 border-success ps-3 mb-4 notification-item">
                                <div className="d-flex justify-content-between">
                                    <h6 className="mb-1">New Batch of Paracetamol Arrived</h6>
                                    <small className="text-muted">Yesterday, 4:15 PM</small>
                                </div>
                                <p className="text-muted small mb-1">To: <strong>All Users</strong></p>
                                <p className="mb-0 text-dark">Inventory has been restocked for the upcoming weekend camps.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    );
}
