<?php include '../../includes/header.php'; ?>


            <div class="main-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Analytics & Reports</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="layout.html">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Reports</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-custom" id="exportPdfBtn">
                            <i class="fa-solid fa-file-pdf me-2"></i> Export PDF
                        </button>
                        <button class="btn btn-outline-custom" id="exportCsvBtn">
                            <i class="fa-solid fa-file-csv me-2"></i> Export CSV
                        </button>
                    </div>
                </div>

                <!-- Filters Card -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <form class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-medium">Date Range</label>
                                <select class="form-select">
                                    <option value="7">Last 7 Days</option>
                                    <option value="30" selected>Last 30 Days</option>
                                    <option value="90">Last 3 Months</option>
                                    <option value="year">This Year</option>
                                    <option value="custom">Custom Range...</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-medium">Healthcare Center</label>
                                <select class="form-select">
                                    <option value="all">All Centers</option>
                                    <option value="1">City General Hospital</option>
                                    <option value="2">Northside Clinic</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-medium">Report Type</label>
                                <select class="form-select">
                                    <option value="overview">General Overview</option>
                                    <option value="patients">Patient Demographics</option>
                                    <option value="medicines">Medicine Utilization</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-primary-custom w-100">Apply Filters</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-bold text-dark mb-4">Camp Performance & Patient Outreach</h6>
                                <div style="height: 350px;">
                                    <canvas id="performanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Tabular Data -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
                            <h6 class="fw-bold text-dark mb-0">Detailed Data Breakdown</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="reportsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="ps-4">Month</th>
                                        <th scope="col">Camps Conducted</th>
                                        <th scope="col">Patients Registered</th>
                                        <th scope="col">Medicines Dispatched</th>
                                        <th scope="col" class="text-end pe-4">Success Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-4 fw-medium text-dark">July 2026</td>
                                        <td>22</td>
                                        <td>3,400</td>
                                        <td>8,500 Units</td>
                                        <td class="text-end pe-4"><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">98%</span></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4 fw-medium text-dark">June 2026</td>
                                        <td>20</td>
                                        <td>3,100</td>
                                        <td>7,200 Units</td>
                                        <td class="text-end pe-4"><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">95%</span></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4 fw-medium text-dark">May 2026</td>
                                        <td>14</td>
                                        <td>2,200</td>
                                        <td>5,100 Units</td>
                                        <td class="text-end pe-4"><span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">88%</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                </div>
            </div>

<!-- Export Script Libraries & Logic -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('reportsTable');
    
    // --- CSV EXPORT ---
    const csvBtn = document.getElementById('exportCsvBtn');
    if (csvBtn) {
        csvBtn.addEventListener('click', function() {
            let csvContent = "";
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(function(row) {
                const cols = row.querySelectorAll('th, td');
                let rowData = [];
                cols.forEach(function(col) {
                    // Extract text cleanly, remove internal commas and newlines
                    let text = col.innerText.replace(/"/g, '""').replace(/(\r\n|\n|\r)/gm, ' ').trim();
                    rowData.push('"' + text + '"');
                });
                csvContent += rowData.join(",") + "\r\n";
            });
            
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", "hmcms_report.csv");
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }

    // --- PDF EXPORT ---
    const pdfBtn = document.getElementById('exportPdfBtn');
    if (pdfBtn) {
        pdfBtn.addEventListener('click', function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'pt', 'a4');
            
            doc.setFontSize(18);
            doc.text("HMCMS - Analytics & Reports", 40, 40);
            
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text("Exported on: " + new Date().toLocaleString(), 40, 60);

            doc.autoTable({
                html: '#reportsTable',
                startY: 80,
                theme: 'grid',
                styles: { fontSize: 10, cellPadding: 6 },
                headStyles: { fillColor: [38, 33, 92], textColor: 255 }, // Match primary color #26215C
                alternateRowStyles: { fillColor: [238, 237, 254] }, // Primary light for zebra striping
            });
            
            doc.save("hmcms_report.pdf");
        });
    }
});
</script>
<?php include '../../includes/footer.php'; ?>
