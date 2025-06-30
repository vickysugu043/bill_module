<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// $brn_id = $_POST["brn_id"] ?? null;
// $finYearCode = $_POST["finYearCode"] ?? null;

$brn_id = 15;
$finYearCode = 2526;

if (empty($brn_id) || empty($finYearCode)) {
    echo json_encode(["status" => "error", "message" => "Branch ID and Financial Year Code are required!"]);
    exit();
}

// Database configuration
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
        "server" => "WIN-TK201B2EL0C\DAIVELHO",
        "database" => "DAIVELHO",
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
$conn = sqlsrv_connect($selectedDb["server"], [
    "Database" => $selectedDb["database"],
    "Uid" => $selectedDb["user"],
    "PWD" => $selectedDb["password"],
    "CharacterSet" => "UTF-8"
]);

if ($conn === false) {
    error_log(print_r(sqlsrv_errors(), true));
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed!",
        "errors" => sqlsrv_errors()
    ]);
    exit();
}

$query = "SELECT bstrtrnno, CONVERT(DATE, btrndate) AS btrndate FROM TrnBillHdr WHERE bwrkunt = ? AND btrnyr = ? ORDER BY btrnno DESC";
$params = [$brn_id, $finYearCode];
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt == false) {
    echo json_encode([
        "status" => "error",
        "message" => "Query execution failed",
        "errors" => sqlsrv_errors()
    ]);
    exit();
}

$trn_no = [];
while ($row = sqlsrv_fetch_array($stmt)) {
    $trn_no[] = [
        "bstrtrnno" => $row["bstrtrnno"],
        "btrndate" => $row["btrndate"]->format("Y-m-d")
    ];
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

echo json_encode([
    "status" => "success",
    "data" => $trn_no
]);