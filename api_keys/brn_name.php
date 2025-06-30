<?php
header("Content-Type: application/json");
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

// Fetch BRN name
$sql = "SELECT wuname AS brn_name FROM mstworkunit WHERE wuid = ?";
$params = [$empwrkunit];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch BRN name",
        "details" => print_r(sqlsrv_errors(), true)
    ]);
    exit();
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$brn_name = $row['brn_name'] ?? '';

echo json_encode([
    "status" => "success",
    "brn_name" => $brn_name
]);
?>