<!DOCTYPE html>
<html>

<head>
    <title>Lead Report</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/leadreport.css">
    <!-- Add these lines in your <head> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="logo">
            <a href="index.php">
                <img src="images/bg.png" alt="Logo" height="50" />
            </a>
        </div>
        <div class="header">
            <h1>Lead Report</h1>
            <div class="results-count" id="results-count">Loading...</div>
            <a class="dashboard-link" href="index.php">
                <i class="fa fa-dashboard"></i> Dashboard
            </a>
        </div>
        <div class="filter-form">
            <form id="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="from_date">From Date</label>
                        <input type="date" name="from_date" id="from_date" required>
                    </div>
                    <div class="filter-group">
                        <label for="to_date">To Date</label>
                        <input type="date" name="to_date" id="to_date" required>
                    </div>
                    <div class="filter-group">
                        <label for="mobile">Mobile Number</label>
                        <input type="text" name="mobile" id="mobile" placeholder="Filter by mobile">
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="button" class="btn btn-secondary" id="reset-btn">Reset</button>
                    <button type="submit" class="btn btn-primary">Search Leads</button>
                </div>
                <!-- Hidden Table for Export/Print -->
                <table id="export-table" style="display:none;">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Mobile</th>
                            <th>Lead Type</th>
                            <th>Status</th>
                            <th>Call Time</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="export-table-body">
                        <!-- Filled by JS -->
                    </tbody>
                </table>
                <div class="results-header">
                    <div class="results-count" id="results-count-2">Loading...</div>
                    <div class="export-actions">
                        <button type="button" class="btn btn-success"
                            onclick="exportTableToExcel('export-table', 'lead_report_' + new Date().toISOString().slice(0,10))">
                            <i class="fa fa-file-excel-o"></i> Export Excel
                        </button>
                        <button type="button" class="btn btn-danger"
                            onclick="exportTableToPDF('export-table', 'lead_report_' + new Date().toISOString().slice(0,10))">
                            <i class="fa fa-file-pdf-o"></i> Export PDF
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="pagination-search-row"
            style="display: flex; justify-content: space-between; align-items: center; margin: 15px 0;">
            <div>
                <input type="text" id="search-box" placeholder="Search by name or remarks..."
                    style="padding:6px 12px; border-radius:6px; border:1px solid #ccc; min-width:220px;">
            </div>
            <div id="pagination-controls" style="display: flex; gap: 8px; align-items: center;"></div>
        </div>
        <div class="results-container" id="results-container">
            <div class="no-data">Loading...</div>
        </div>
    </div>
    <script src="js/lead_rpt.js"></script>
</body>

</html>