<?php
ob_clean(); // Clears any accidental output
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Start session
session_start();

// Check if employee is logged in
if (empty($_SESSION['empid'])) {
    echo json_encode(["status" => "error", "message" => "Session expired! Please log in again."]);
    exit();
}

$emp_code = $_SESSION['empid'];
$empwrkunit = $_SESSION['empwrkunit'] ?? '';

// Database configuration
$databaseConfig = [
    "server" => "WIN-TK201B2EL0C\DAIVELHO",
    "database" => "DAIVELHO",
    "user" => "sa",
    "password" => "Admin@1234"
];

// Establish database connection
try {
    $serverName = $databaseConfig["server"];
    $connectionInfo = [
        "Database" => $databaseConfig["database"],
        "Uid" => $databaseConfig["user"],
        "PWD" => $databaseConfig["password"],
        "CharacterSet" => "UTF-8"
    ];

    $conn = sqlsrv_connect($serverName, $connectionInfo);

    if ($conn === false) {
        throw new Exception("Database connection failed: " . print_r(sqlsrv_errors(), true));
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection error",
        "details" => $e->getMessage()
    ]);
    exit();
}

$from_date = $_GET['from_date'] ?? date('Y-m-d');
$to_date = $_GET['to_date'] ?? date('Y-m-d');
$mobile = $_GET['mobile'] ?? null;

// Fetch leads
$leads = [];
if ($conn) {
    $sql = "SELECT CUS.CustName, CUS.CustMobileNo, LED.clempid, LED.clcustcd, LED.cltime, LED.clleadtime, LED.clleadtype, LED.clremarks 
            FROM TrnCustLead LED
            INNER JOIN MstCustomer CUS ON CUS.CustCd = LED.clcustcd AND LED.clcustmobile = CUS.CustMobileNo
            WHERE LED.cltime BETWEEN ? AND ?";
    
    $params = array(
        array($from_date . " 00:00:00", SQLSRV_PARAM_IN),
        array($to_date . " 23:59:59", SQLSRV_PARAM_IN)
    );

    if (!empty($mobile)) {
        $sql .= " AND CUS.CustMobileNo = ?";
        $params[] = array($mobile, SQLSRV_PARAM_IN);
    }

    if (!empty($emp_code)) {
        $sql .= " AND LED.clempid = ?";
        $params[] = array($emp_code, SQLSRV_PARAM_IN);
    }

    $sql .= " ORDER BY LED.cltime DESC";

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        echo json_encode([
            "status" => "error",
            "message" => "Query execution failed",
            "details" => print_r(sqlsrv_errors(), true)
        ]);
        exit();
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $leads[] = $row;
    }
}

$lead_count = count($leads);

echo json_encode([
    "status" => "success",
    "count" => $lead_count,
    "leads" => $leads
]);
exit();
?>