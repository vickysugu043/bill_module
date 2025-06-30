<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Start session early
session_start();

// Accept empid from GET or POST (allow both for flexibility)
$empid = $_GET["empid"] ?? $_POST["empid"] ?? "";

// Validate if empid is provided
if (empty($empid)) {
    echo json_encode(["status" => "error", "message" => "EmpID is required!"]);
    exit();
}

// Static DB config
$databaseConfig = [
    "server"   => "WIN-TK201B2EL0C\DAIVELHO",
    "database" => "DAIVELHO",
    "user"     => "sa",
    "password" => "Admin@1234"
];

// DB connection
$conn = sqlsrv_connect($databaseConfig["server"], [
    "Database"     => $databaseConfig["database"],
    "Uid"          => $databaseConfig["user"],
    "PWD"          => $databaseConfig["password"],
    "CharacterSet" => "UTF-8"
]);

if ($conn === false) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed!",
        "errors" => sqlsrv_errors()
    ]);
    exit();
}

// Query with prepared statement
$sql = "SELECT empid, empname, empwrkunit, empdesig FROM mstemp WHERE empid = ? AND active = 1";
$stmt = sqlsrv_prepare($conn, $sql, [ &$empid ]);

if (!$stmt || !sqlsrv_execute($stmt)) {
    echo json_encode([
        "status" => "error",
        "message" => "Query execution failed!",
        "errors" => sqlsrv_errors()
    ]);
    exit();
}

// Fetch result
$result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if ($result) {
    // Set session variables for employee details
    $_SESSION['empid']            = $result['empid'];
    $_SESSION['empname']          = $result['empname'];
    $_SESSION['empwrkunit']       = $result['empwrkunit'];
    $_SESSION['empdesig']         = $result['empdesig'];
    $_SESSION['login_time_stamp'] = time();

    // Fetch designation name
    $empdesig = $result['empdesig'];
    $sqlDesig = "SELECT Desigdesc FROM MstDesignation WHERE DesigCode = ?";
    $desigParams = [$empdesig];
    $stmtDesig = sqlsrv_prepare($conn, $sqlDesig, $desigParams);

    $DesigName = '';

    if ($stmtDesig && sqlsrv_execute($stmtDesig)) {
        $rowDesig = sqlsrv_fetch_array($stmtDesig, SQLSRV_FETCH_ASSOC);
        $DesigName = $rowDesig['Desigdesc'] ?? '';
    }

    // Store designation name in session
    $_SESSION['empdesig_name'] = $DesigName;

    // Output JSON response
    echo json_encode([
        "status"  => "success",
        "message" => "Login successful",
        "data"    => $result,
        "desig"   => $DesigName
    ]);
}else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid employee ID or inactive account"
    ]);
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>