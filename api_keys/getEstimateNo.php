<?php
header("Content-Type: application/json");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$brn_id = isset($_GET['brn_id']) ? trim($_GET['brn_id']) : '';

if (empty($brn_id)) {
    echo json_encode([
        "status" => "error",
        "message" => "Branch ID is required!"
    ]);
    exit();
}

$databaseConnections = [
    "1" => [
        "server" => "WIN-TK201B2EL0C\DAIVELHO",
        "database" => "DAIVELHO",
        "user" => "sa",
        "password" => "Admin@1234"
    ],
    "2" => [
        "server" => "10.0.205.2\SQL2019",
        "database" => "DAIVELCUMBUMTEST",
        "user" => "sa",
        "password" => "Admin@1234"
    ],
    "3" => [
        "server" => "10.0.203.2\SIVAKASI",
        "database" => "DAIVELSIVAKASI",
        "user" => "sa",
        "password" => "Admin@1234"
    ],
    "4" => [
        "server" => "10.0.204.2\NAGERCOIL",
        "database" => "DAIVELNAGERCOIL",
        "user" => "sa",
        "password" => "Admin@1234"
    ],
    "5" => [
        "server" => "10.0.202.2\DAIVELTNV",
        "database" => "DAIVELTNV",
        "user" => "sa",
        "password" => "Admin@1234"
    ],
    "6" => [
        "server" => "10.0.206.2\TUTICORIN",
        "database" => "DAIVELTUTICORIN",
        "user" => "sa",
        "password" => "Admin@1234"
    ],
    "8" => [
        "server" => "10.0.208.2\THENI",
        "database" => "DAIVELTHENI",
        "user" => "sa",
        "password" => "Admin@1234"
    ],
    "9" => [
        "server" => "10.0.209.2\TENKASI",
        "database" => "DAIVELTENKASI",
        "user" => "sa",
        "password" => "Admin@1234"
    ],
    "10" => [
        "server" => "10.0.210.2\CBE2",
        "database" => "DAIVELCBE2",
        "user" => "sa",
        "password" => "Admin@1234"
    ],
    "11" => [
        "server" => "10.0.211.2\PALANI",
        "database" => "DAIVELPALANI",
        "user" => "sa",
        "password" => "Admin@1234"
    ],
    "12" => [
        "server" => "10.0.212.2\MDHOUSE",
        "database" => "DAIVELMDHOUSE",
        "user" => "sa",
        "password" => "Admin@1234"
    ],
    "13" => [
        "server" => "10.0.213.2\DHARAPURAM",
        "database" => "DAIVELDHARAPURAM",
        "user" => "sa",
        "password" => "Admin@1234"
    ],
    "15" => [
        "server" => "10.0.215.2\NHKTM",
        "database" => "DAIVELNHKTM",
        "user" => "sa",
        "password" => "Admin@1234"
    ],
    "16" => [
        "server" => "10.0.216.2\ARUPPUKKOTTAI",
        "database" => "DAIVELARUPPUKKOTTAI",
        "user" => "sa",
        "password" => "Admin@1234"
    ],
    "17" => [
        "server" => "10.0.217.2\OCEANSAPPHIRE",
        "database" => "DAIVELOCEANSAPPHIRE",
        "user" => "sa",
        "password" => "Admin@1234"
    ]
];

if (!array_key_exists($brn_id, $databaseConnections)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid Branch ID!"
    ]);
    exit();
}

$selectedDb = $databaseConnections[$brn_id];

$serverName = $selectedDb["server"];
$connectionOptions = [
    "Database" => $selectedDb["database"],
    "Uid" => $selectedDb["user"],
    "PWD" => $selectedDb["password"],
    "CharacterSet" => "UTF-8"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed for branch ID: $brn_id",
        "errors" => sqlsrv_errors()
    ]);
    exit();
}

$query = "SELECT BranchId,BranchName FROM MstOtherConcern";
$stmt = sqlsrv_query($conn, $query);

if ($stmt === false) {
    echo json_encode([
        "status" => "error",
        "message" => "Query execution failed",
        "errors" => sqlsrv_errors()
    ]);
    exit();
}

$customers = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $customers[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);


echo json_encode([
    "status" => "success",
    "data"   => $customers
]);
