<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Start session early
session_start();

$emp_code = $_SESSION['empid'] ?? '';
$empwrkunit = $_SESSION['empwrkunit'] ?? '';

if (empty($emp_code)) {
    echo json_encode(["status" => "error", "message" => "Session expired! Please log in again."]);
    exit();
}

// Static DB config
$databaseConfig = [
    "server" => "WIN-TK201B2EL0C\DAIVELHO",
    "database" => "DAIVELHO",
    "user" => "sa",
    "password" => "Admin@1234"
];

// DB connection with error handling
try {
    $conn = sqlsrv_connect($databaseConfig["server"], [
        "Database" => $databaseConfig["database"],
        "Uid" => $databaseConfig["user"],
        "PWD" => $databaseConfig["password"],
        "CharacterSet" => "UTF-8"
    ]);

    if ($conn === false) {
        throw new Exception("Database connection failed: " . print_r(sqlsrv_errors(), true));
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
    exit();
}

// Initialize dashboard data structure
$dashboard_data = [
    'customers' => ['total' => 0, 'new_today' => 0],
    'today_calls' => 0,
    'sales_leads' => ['total' => 0, 'today' => 0],
    'chit_leads' => ['total' => 0, 'today' => 0],
    'visits_today' => 0
];

// Today's date for consistent comparison
$today = date('Y-m-d');

try {
    // 1. Get customer metrics (total and new today) - Using MstCRM table
    $sql = "SELECT 
                COUNT(*) AS total, 
                SUM(CASE WHEN CONVERT(date, crmeffdate) = ? THEN 1 ELSE 0 END) AS new_today
            FROM MstCRM 
            WHERE crmempid = ? AND crmwrkunt = ?";
    $customer_params = [$today, $emp_code, $empwrkunit];
    $dashboard_data['customers'] = executeCountQuery($conn, $sql, $customer_params, ['total', 'new_today']);

    // 2. Get today's calls - Using TrnCustLead table
    $sql = "SELECT COUNT(*) AS total 
            FROM TrnCustLead 
            WHERE clempid = ? AND clwrkunt = ? AND CONVERT(date, cltime) = ?";
    $call_params = [$emp_code, $empwrkunit, $today];
    $result = executeCountQuery($conn, $sql, $call_params, ['total']);
    $dashboard_data['today_calls'] = $result['total'] ?? 0;

    // 3. Get sales leads (total and today) - Using TrnCustLead table
    $sql = "SELECT 
                COUNT(*) AS total, 
                SUM(CASE WHEN CONVERT(date, clleadtime) = ? THEN 1 ELSE 0 END) AS today
            FROM TrnCustLead 
            WHERE clempid = ? AND clwrkunt = ? AND clleadtype = 'S'"; // Assuming 'S' is for Sales
    $dashboard_data['sales_leads'] = executeCountQuery($conn, $sql, [$today, $emp_code, $empwrkunit], ['total', 'today']);

    // 4. Get chit leads (total and today) - Using TrnCustLead table
    $sql = "SELECT 
                COUNT(*) AS total, 
                SUM(CASE WHEN CONVERT(date, clleadtime) = ? THEN 1 ELSE 0 END) AS today
            FROM TrnCustLead 
            WHERE clempid = ? AND clwrkunt = ? AND clleadtype = 'C'"; // Assuming 'C' is for Chit
    $dashboard_data['chit_leads'] = executeCountQuery($conn, $sql, [$today, $emp_code, $empwrkunit], ['total', 'today']);

    // 5. Get visits today - Using TrnCustLead table
    $sql = "SELECT COUNT(*) AS total 
            FROM TrnCustLead 
            WHERE clempid = ? AND clwrkunt = ? AND clleadtype = 'V' AND CONVERT(date, clleadtime) = ?"; // Assuming 'V' is for Visit
    $result = executeCountQuery($conn, $sql, [$emp_code, $empwrkunit, $today], ['total']);
    $dashboard_data['visits_today'] = $result['total'] ?? 0;

    // Return successful response
    echo json_encode([
        "status" => "success",
        "data" => $dashboard_data
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage(),
        "data" => $dashboard_data // Return partial data if available
    ]);
} finally {
    // Close connection if it exists
    if ($conn) {
        sqlsrv_close($conn);
    }
}

/**
 * Helper function to execute count queries and return results
 */
function executeCountQuery($conn, $sql, $params, $expectedFields) {
    $result = array_fill_keys($expectedFields, 0);
    $stmt = sqlsrv_prepare($conn, $sql, $params);
    
    if (!$stmt || !sqlsrv_execute($stmt)) {
        throw new Exception("Query execution failed: " . print_r(sqlsrv_errors(), true));
    }
    
    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        foreach ($expectedFields as $field) {
            $result[$field] = $row[$field] ?? 0;
        }
    }
    
    sqlsrv_free_stmt($stmt);
    return $result;
}
?>