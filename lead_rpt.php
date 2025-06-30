<?php
// ==================== INITIALIZATION ====================
$emp_code = $_GET['emp_code'] ?? null;

// ==================== CONFIGURATION ====================
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log/dashboard_errors.log');
date_default_timezone_set('Asia/Kolkata');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'oceansapphire_live_demo');
define('DB_USER', 'oceansapphire_userocean');
define('DB_PASS', 'P$QxrOO1lR1V');

// ==================== DATABASE CONNECTION ====================
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die(json_encode(['error' => 'System temporarily unavailable. Please try again later.']));
}

// Get filter parameters from request
$from_date = $_GET['from_date'] ?? date('Y-m-01'); // Default to start of current month
$to_date = $_GET['to_date'] ?? date('Y-m-d');     // Default to today
$mobile = $_GET['mobile'] ?? null;
$emp_code = $_GET['emp_code'] ?? null; // Optional employee code filter

// Prepare base query
$query = "SELECT * FROM crm_followup WHERE 
          (call_time BETWEEN :from_date AND :to_date + INTERVAL 1 DAY)";
$params = [
    ':from_date' => $from_date,
    ':to_date' => $to_date
];

// Add mobile filter if provided
if ($mobile) {
    $query .= " AND cus_mob = :mobile";
    $params[':mobile'] = $mobile;
}

// Add employee code filter if provided
if ($emp_code) {
    $query .= " AND ec_no = :emp_code";
    $params[':emp_code'] = $emp_code;
}

// Add lead filter (only show records with lead_type)
$query .= " AND lead_type IS NOT NULL";

// Order by most recent first
$query .= " ORDER BY call_time DESC";

try {
    // Prepare and execute query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Lead report query failed: " . $e->getMessage());
    die("Error generating lead report. Please try again later.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lead Report</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="icon" href="img/apple-touch-icon.png">
    <style>
        :root {
            --primary-color: #1e2c4d;
            --accent-color: #2a3d66;
            --text-color: #333;
            --text-light: #666;
            --border-radius: 8px;
            --box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #1e2c4d;
            color: var(--text-color);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
            color: #fff;
        }
        
        .header h1 {
            margin: 0;
            color: #fff;
            font-size: 28px;
        }
        
        .filter-form {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--box-shadow);
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--text-light);
            font-size: 14px;
        }
        
        .filter-group input {
            width: 90%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 14px;
        }
        
        .filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: var(--border-radius);
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--accent-color);
        }
        
        .btn-secondary {
            background-color: #f0f0f0;
            color: var(--text-color);
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
        }
        
        .results-container {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .results-count {
            font-weight: 500;
            color: #fff;
        }
        
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .lead-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 15px;
            color: var(--text-color);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border-top: 4px solid var(--accent-color);
        }
        
        .lead-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .lead-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .lead-id {
            font-weight: bold;
            font-size: 16px;
            color: var(--primary-color);
        }
        
        .lead-mobile {
            font-weight: bold;
            color: var(--text-light);
            font-size: 14px;
        }
        
        .lead-details {
            margin-bottom: 10px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .detail-label {
            font-weight: bold;
            color: var(--text-light);
            font-size: 14px;
        }
        
        .detail-value {
            color: var(--text-color);
            font-weight: 500;
            font-size: 14px;
            text-align: right;
        }
        
        .detail-row.remarks {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .detail-row.remarks .detail-value {
            margin-top: 5px;
            text-align: left;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: var(--text-light);
            font-style: italic;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge-sales {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-chit {
            background-color: #fff3cd;
            color: #856404;
        }
        
        @media (max-width: 768px) {
            .cards-container {
                grid-template-columns: 1fr;
            }
            
            .filter-row {
                flex-direction: column;
                gap: 10px;
            }
            
            .filter-group {
                min-width: 100%;
            }
            
            .filter-group input{
                width: 96%;
            }
            
            .logo {
                text-align: center !important;
            }
        }
        
        .dashboard-link {
            text-decoration: none;
            font-size: 18px;
            color: #fff;
            font-weight: bold;
            margin-left: auto;
            float: right;
            margin-top: -8% !important;
        }
        
        .dashboard-link:hover {
            text-decoration: none;
        }
        
        .logo {
            text-align: left;
            padding-left: 20px;
            margin-bottom: 10%;
         }
         
         @media print {
            .filter-form,
            .dashboard-link,
            .header h1,
            .logo {
                display: none !important;
            }
        
            body {
                background: white !important;
                color: black !important;
            }
        
            .lead-card {
                page-break-inside: avoid;
            }
            
            .d-print-none { 
                display: none !important; 
            }
        }
        
        .export-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        /* Make buttons more visible on mobile */
        @media (max-width: 768px) {
            .results-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .export-actions {
                width: 100%;
            }
            
            .export-actions .btn {
                flex: 1;
                padding: 10px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="container">
        <div class="logo">
            <a href="index.php?emp_code=<?php echo urlencode($emp_code); ?>">
                <img src="images/bg.png" alt="Logo" height="50" />
            </a>
          </div>
        <div class="header">
            <h1>Lead Report</h1>
            <div class="results-count"><?= count($leads) ?> <?= count($leads) === 1 ? 'lead' : 'leads' ?> found</div>
            
            <a class="dashboard-link" href="index.php?emp_code=<?= urlencode($emp_code); ?>">
               <i class="fa fa-dashboard"></i> Dashboard
            </a>
        </div>
        
        <div class="filter-form">
            <form method="get">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="from_date">From Date</label>
                        <input type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>" required>
                    </div>
                    <div class="filter-group">
                        <label for="to_date">To Date</label>
                        <input type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>" required>
                    </div>
                    <div class="filter-group">
                        <label for="mobile">Mobile Number</label>
                        <input type="text" name="mobile" value="<?= htmlspecialchars($mobile ?? '') ?>" placeholder="Filter by mobile">
                    </div>
                </div>
                
                <?php if (isset($_GET['emp_code'])): ?>
                    <input type="hidden" name="emp_code" value="<?= htmlspecialchars($_GET['emp_code']) ?>">
                <?php endif; ?>
                
                <div class="filter-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='?'">Reset</button>
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
                    <tbody>
                        <?php if (!empty($leads)) : ?>
                            <?php foreach ($leads as $lead) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($lead['cus_name']) ?></td>
                                    <td><?= htmlspecialchars($lead['cus_mob']) ?></td>
                                    <td><?= htmlspecialchars($lead['lead_type'] == 'S' ? 'Sales' : ($lead['lead_type'] == 'C' ? 'Chit' : 'N/A')) ?></td>
                                    <td><?= htmlspecialchars($lead['status']) ?></td>
                                    <td><?= htmlspecialchars($lead['call_time']) ?></td>
                                    <td><?= htmlspecialchars($lead['remarks']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">No data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="results-header">
                    <div class="results-count"><?= count($leads) ?> <?= count($leads) === 1 ? 'lead' : 'leads' ?> found</div>
                    <div class="export-actions">
                        <button type="button" class="btn btn-success" onclick="exportTableToExcel('export-table', 'lead_report_<?= date('Y-m-d') ?>')">
                            <i class="fa fa-file-excel-o"></i> Export Excel
                        </button>
                        <button type="button" class="btn btn-primary" onclick="printHiddenTable('export-table')">
                            <i class="fa fa-print"></i> Print Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="results-container">
            <?php if (empty($leads)): ?>
                <div class="no-data">No leads found for the selected filters</div>
            <?php else: ?>
                <div class="cards-container">
                    <?php foreach ($leads as $lead): ?>
                        <div class="lead-card">
                            <div class="lead-header">
                                <div class="lead-id">Lead #<?= htmlspecialchars($lead['id']) ?></div>
                                <div class="lead-mobile"><?= htmlspecialchars($lead['cus_mob']) ?></div>
                            </div>
                            
                            <div class="lead-details">
                                <div class="detail-row">
                                    <span class="detail-label">Employee Code:</span>
                                    <span class="detail-value"><?= htmlspecialchars($lead['ec_no']) ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Call Time:</span>
                                    <span class="detail-value"><?= date('d M Y H:i', strtotime($lead['call_time'])) ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Lead Type:</span>
                                    <span class="detail-value">
                                        <span class="badge <?= $lead['lead_type'] == 'S' ? 'badge-sales' : 'badge-chit' ?>">
                                            <?= $lead['lead_type'] == 'S' ? 'Sales' : ($lead['lead_type'] == 'C' ? 'Chit' : 'N/A') ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Follow-up Time:</span>
                                    <span class="detail-value"><?= $lead['lead_time'] ? date('d M Y H:i', strtotime($lead['lead_time'])) : 'N/A' ?></span>
                                </div>
                                <div class="detail-row remarks">
                                    <span class="detail-label">Remarks:</span>
                                    <span class="detail-value"><?= htmlspecialchars($lead['remarks'] ?? 'No remarks') ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function exportTableToExcel(tableID, filename = '') {
            try {
                const table = document.getElementById(tableID);
                if (!table) {
                    throw new Error('Table not found');
                }
        
                // Check if table has data rows (beyond header)
                const dataRows = table.querySelectorAll('tbody tr');
                if (dataRows.length === 0) {
                    alert('No data available to export');
                    return;
                }
        
                // Create HTML string
                let html = table.outerHTML;
                
                // Create blob
                const blob = new Blob([html], {type: 'application/vnd.ms-excel'});
                filename = filename || 'lead_report_' + new Date().toISOString().slice(0, 10);
                
                // For IE
                if (navigator.msSaveBlob) {
                    navigator.msSaveBlob(blob, filename + '.xls');
                } 
                // For other browsers
                else {
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = filename + '.xls';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            } catch (error) {
                console.error('Export error:', error);
                alert('Error during export: ' + error.message);
            }
        }
        
        function printHiddenTable(tableID) {
            const table = document.getElementById(tableID);
            const style = `
                <style>
                    body { font-family: Arial; margin: 20px; }
                    h1 { color: #1e2c4d; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th { background: #1e2c4d; color: white; text-align: left; padding: 8px; }
                    td { padding: 8px; border: 1px solid #ddd; }
                    tr:nth-child(even) { background: #f2f2f2; }
                    .print-header { margin-bottom: 20px; }
                    .print-title { font-size: 24px; font-weight: bold; }
                    .print-date { font-size: 14px; color: #666; }
                </style>
            `;
            
            const win = window.open('', '', 'width=800,height=600');
            win.document.write(`
                <html>
                    <head>
                        <title>Lead Report</title>
                        ${style}
                    </head>
                    <body>
                        <div class="print-header">
                            <div class="print-title">Lead Report</div>
                            <div class="print-date">Generated on ${new Date().toLocaleString()}</div>
                        </div>
                        ${table.outerHTML}
                    </body>
                </html>
            `);
            
            win.document.close();
            win.focus();
            setTimeout(() => {
                win.print();
                win.close();
            }, 500);
        }
    </script>
</body>
</html>