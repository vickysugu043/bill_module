<?php
@session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Session & constants
$brn_id = $_SESSION['empwrkunit'];
$peempid = $_SESSION['empid'];
$finYearCode = 2526;

// Input validation
if (empty($input) || empty($input["rowData"]) || !$brn_id || !$peempid) {
    echo json_encode(["status" => "error", "message" => "Missing session or input data!"]);
    exit();
}

$rowData = $input["rowData"];
$entryBy = $input["entryBy"];
$gross_Amt = floatval($input["gross_Amt"]);
$nett_amount = floatval($input["nett_amount"]);
$round_off = floatval($input["round_off"]);

// Database connections
$databaseConnections = [
    "1" => [
        "server" => "WIN-TK201B2EL0C\DAIVELHO",
        "database" => "DAIVELHO",
        "user" => "sa",
        "password" => "Admin@1234"
    ],
    "2" => [
        "server" => "10.0.205.2\SQL2019",
        "database" => "DAIVELCUMBUM",
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

// Check if branch ID is valid
if (!isset($databaseConnections[$brn_id])) {
    echo json_encode(["status" => "error", "message" => "Invalid Branch ID!"]);
    exit();
}

$db = $databaseConnections[$brn_id];
$conn = sqlsrv_connect($db["server"], [
    "Database" => $db["database"],
    "Uid" => $db["user"],
    "PWD" => $db["password"],
    "CharacterSet" => "UTF-8"
]);

if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Database connection failed!", "errors" => sqlsrv_errors()]);
    exit();
}

// Get next petrnno
$petrnnoQuery = "SELECT ISNULL(MAX(petrnno), 0) + 1 AS petrnno FROM TrnItmEstHdr WHERE petrnyr = ? AND pewrkunt = ?";
$stmt = sqlsrv_prepare($conn, $petrnnoQuery, [$finYearCode, $brn_id]);
sqlsrv_execute($stmt);
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$petrnno = $row['petrnno'];
$pestrtrnno = $petrnno;

// Common fields
$currentDateTime = date("Y-m-d H:i:s");
$chmc = $_SERVER['REMOTE_ADDR'];
$peempwrkunt = $brn_id;
$entrytime = $currentDateTime;
$peremarks = "";
$approve = "Y";

// Begin transaction
sqlsrv_begin_transaction($conn);

// Insert header
$insertHdrQuery = "INSERT INTO TrnItmEstHdr (pewrkunt, petrnno, petrnyr, pestrtrnno, petrndate, pemettype, peempid, peempwrkunt, peremarks, chby, chon, chmc, pegrosstot, peroundoff, penettot, billsts, pedisamt, pediscount, petype, entrytime, approve) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$hdrParams = [$brn_id, $petrnno, $finYearCode, $pestrtrnno, date("Y-m-d"), "E", $peempid, $peempwrkunt, $peremarks, $peempid, $currentDateTime, $chmc, $gross_Amt, $round_off, $nett_amount, "N", 0.00, "N", "Z", $entrytime, $approve];
$hdrStmt = sqlsrv_prepare($conn, $insertHdrQuery, $hdrParams);

if (!$hdrStmt || !sqlsrv_execute($hdrStmt)) {
    sqlsrv_rollback($conn);
    echo json_encode(["status" => "error", "message" => "Failed to insert header!", "errors" => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit();
}

// Process detail records
if (count($rowData) > 0) {
    $rowDatas = $rowData;
} else {
    echo json_encode(["status" => "error", "message" => "No records found to process."]);
    exit();
}

foreach ($rowDatas as $i => $rec) {
    $nettWt = floatval($rec["nettWt"]);
    $stoneWt = floatval($rec["stoneWt"]);
    $grossWt = $nettWt + $stoneWt;
    $rate = floatval($rec["rate"]);
    $grossAmt = floatval($rec["grossAmt"]);
    $taxVal = floatval($rec["taxVal"]);
    $fin_Amt = $grossAmt + $taxVal;

    $pedmetaltype = trim($rec["bsmetaltype"]); // Always trim it first
    $purity = in_array($pedmetaltype, ["GW", "AG", "GA"]) ? 0 : floatval($pedmetaltype);

    // Prepare detail record parameters
    $dtlParams = [
        $brn_id, $petrnno, $finYearCode, $i + 1,$rec["barcode"], $rec["bsitmid"], $rec["bsitmctgid"], $rec["bsitmsubctgid"],$purity, 1, $grossWt, $stoneWt, $nettWt, $rate, 0, 0, floatval($rec["wstPer"]), 0,floatval($rec["stoneVal"]), $grossAmt, $rec["itgstper"], $taxVal, 0, $fin_Amt,$entryBy, $currentDateTime, $chmc, 
        $rec["bsmetaltype"], $rec["bscarat"], 0,floatval($rec["hmcVal"]), 0, floatval($rec["offerDis"]), $entryBy, $peempwrkunt, floatval($rec["bsdiawt"])
    ];

    // Insert detail record
    // $insertDtlQuery = "INSERT INTO TrnItmEstDtl (pedwrkunt, pedtrnno, pedtrnyr, pedslno, pestkbarcd, peditmid, pedctgid, pedsubctgid, pedpurity, pednoofpcs, pedgrosswt, pedstonewt, pednetwt, pedrate, pedmcper, pedmcchrg, pedwstper, pedwstchrg, pestoneval, pedgrossamt, petaxstruccd, petaxval, peothval, pefinalamt, chby, chon, chmc, pedmetaltype, pedstonecarat, peddiscrate, pedhallmrkchrg, pedstndisc, pedofferdis, pedempid, pedempwrkunt, peddiawt)
    // VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // echo "INSERT INTO TrnItmEstDtl (pedwrkunt, pedtrnno, pedtrnyr, pedslno, pestkbarcd, peditmid, pedctgid, pedsubctgid, pedpurity, pednoofpcs, pedgrosswt, pedstonewt, pednetwt, pedrate, pedmcper, pedmcchrg, pedwstper, pedwstchrg, pestoneval, pedgrossamt, petaxstruccd, petaxval, peothval, pefinalamt, chby, chon, chmc, pedmetaltype, pedstonecarat, peddiscrate, pedhallmrkchrg, pedstndisc, pedofferdis, pedempid, pedempwrkunt, peddiawt)
    // VALUES ($brn_id, $petrnno, $finYearCode, $i + 1,'".$rec["barcode"]."', '".$rec["bsitmid"]."', '".$rec["bsitmctgid"]."', '".$rec["bsitmsubctgid"].".,$purity, 1, $grossWt, $stoneWt, $nettWt, $rate, 0, 0, floatval('".$rec["wstPer"]."'), 0,floatval('".$rec["stoneVal"]."'), $grossAmt, '".$rec["itgstper"]."', $taxVal, 0, $fin_Amt,$entryBy, $currentDateTime, $chmc, '".$rec["bsmetaltype"]."', '".$rec["bscarat"]."', 0,floatval('".$rec["hmcVal"]."'), 0, floatval('".$rec["offerDis"]."'), $entryBy, $peempwrkunt, floatval('".$rec["bsdiawt"]."'))";

    $debugQuery = "INSERT INTO TrnItmEstDtl (
        pedwrkunt, pedtrnno, pedtrnyr, pedslno, pestkbarcd, peditmid, pedctgid, pedsubctgid,
        pedpurity, pednoofpcs, pedgrosswt, pedstonewt, pednetwt, pedrate, pedmcper, pedmcchrg,
        pedwstper, pedwstchrg, pestoneval, pedgrossamt, petaxstruccd, petaxval, peothval, pefinalamt,
        chby, chon, chmc, pedmetaltype, pedstonecarat, peddiscrate, pedhallmrkchrg, pedstndisc,
        pedofferdis, pedempid, pedempwrkunt, peddiawt
    ) VALUES (
        $brn_id, $petrnno, $finYearCode, " . ($i + 1) . ",
        '{$rec["barcode"]}', '{$rec["bsitmid"]}', '{$rec["bsitmctgid"]}', '{$rec["bsitmsubctgid"]}',
        $purity, 1, $grossWt, $stoneWt, $nettWt, $rate, 0, 0,
        " . floatval($rec["wstPer"]) . ", 0, " . floatval($rec["stoneVal"]) . ", $grossAmt,
        '{$rec["itgstper"]}', $taxVal, 0, $fin_Amt,
        $entryBy, '$currentDateTime', '$chmc', '{$rec["bsmetaltype"]}', '{$rec["bscarat"]}',
        0, " . floatval($rec["hmcVal"]) . ", 0, " . floatval($rec["offerDis"]) . ",
        $entryBy, $peempwrkunt, " . floatval($rec["bsdiawt"]) . "
    );";
    
    // echo $debugQuery . "<br>";

    $dtlStmt = sqlsrv_prepare($conn, $debugQuery, $dtlParams);

    if (!$dtlStmt || !sqlsrv_execute($dtlStmt)) {
        // Log the error and continue with the next record
        error_log("Failed to insert detail record for barcode {$rec['barcode']}: " . print_r(sqlsrv_errors(), true));
        // Optionally: Continue processing the next record
        exit;
    }
}

// Commit transaction
sqlsrv_commit($conn);

// Return success response
echo json_encode(["status" => "success", "message" => "Estimate saved successfully!", "est_no" => $petrnno]);

// Close the connection
sqlsrv_close($conn);
?>
