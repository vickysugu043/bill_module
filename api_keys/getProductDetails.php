<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// $brn_id = 15;
$brn_id = $_GET["brn_id"];
$finYearCode = 2526;
$barcode = $_GET["barcode"];
// $barcode = "116306";

if (empty($brn_id) || empty($finYearCode)) {
    echo json_encode(["status" => "error", "message" => "Branch ID and Financial Year Code are required!"]);
    exit();
}

if (empty($barcode)) {
    echo json_encode(["status" => "error", "message" => "Barcode is required!"]);
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
// print_r($selectedDb);
if ($conn === false) {
    error_log(print_r(sqlsrv_errors(), true));
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed!",
        "errors" => sqlsrv_errors()
    ]);
    exit();
}

// Store current date for reuse
// $currentDate = date("Y-m-d");
$currentDate = date("Y-m-d");

$query = "SELECT STK.bsbarcode AS barcode, ITM.ititemname, SUB.iscname AS name, RTE.grrate AS rate, STK.bswt AS nettWt, MC.makingper, MC.wstper,STK.bsitmid, STK.bsitmsubctgid, 
    STK.bsitmctgid,CASE WHEN TRY_CAST(OFR.ovalue AS NUMERIC(18, 2)) IS NULL THEN 0.00 ELSE TRY_CAST(OFR.ovalue AS NUMERIC(18, 2)) END AS OffDisc,STK.bsmetaltype, STK.bsstoneval, STK.bsstonewt, STK.bscarat, STK.bsdiawt, STK.bshallmrkchrg, MC.makingchrgtype, MC.makingcalcon, MC.wstchrgtype, MC.wstcalcon, ITM.itgstper
FROM BranchOrnamentStock STK
INNER JOIN MStItem ITM ON ITM.ititemid = STK.bsitmid
INNER JOIN MstItemSubCategory SUB ON SUB.iscitmid = STK.bsitmid AND SUB.iscctgid = STK.bsitmctgid AND SUB.iscsubctgid = STK.bsitmsubctgid
INNER JOIN MstGoldRate RTE ON RTE.grtype = STK.bsmetaltype
INNER JOIN mstmakingchrg MC ON MC.wrkunt = STK.bswrkunt AND STK.bsitmid = MC.itmid AND MC.itmctgid = STK.bsitmctgid AND MC.itmsubctgid = STK.bsitmsubctgid
LEFT JOIN mstoffer OFR ON OFR.oitmid = STK.bsitmid AND CAST(OFR.oeffdate AS DATE) >= ? AND CAST(OFR.oeffdate AS DATE) <= ? 
WHERE STK.bsstocksts = 'Y' AND STK.bsbarcode = ? AND CAST(RTE.greffdate AS DATE) = ? 
AND STK.bswrkunt = ? AND RTE.grwrkunt = ?";
 
 $params = [(string)$currentDate, (string)$currentDate, (string)$barcode, (string)$currentDate, (int)$brn_id, (int)$brn_id];

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
// print_r($stmt);
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    error_log("Row fetched: " . print_r($row, true));

    // Validate required fields, if not set default to zero or return an error
    $rate = isset($row["rate"]) ? $row["rate"] : 0;
    $makingPer = isset($row["makingper"]) ? $row["makingper"] : 0;
    $nettWt = isset($row["nettWt"]) ? $row["nettWt"] : 0;
    $wstPer = isset($row["wstper"]) ? $row["wstper"] : 0;
    $stoneVal = isset($row["bsstoneval"]) ? $row["bsstoneval"] : 0;
    $hallMark = isset($row["bshallmrkchrg"]) ? $row["bshallmrkchrg"] : 0;
    $offerDis = isset($row["OffDisc"]) ? $row["OffDisc"] : 0;
    $makingchrgtype = isset($row["makingchrgtype"]) ? $row["makingchrgtype"] : 'V'; // Default to 'V'
    $makingcalcon = isset($row["makingcalcon"]) ? $row["makingcalcon"] : 'NW'; // Default to 'NW'
    $wstchrgtype = isset($row["wstchrgtype"]) ? $row["wstchrgtype"] : 'V'; // Default to 'V'
    $wstcalcon = isset($row["wstcalcon"]) ? $row["wstcalcon"] : 'NW'; // Default to 'NW'
    $bsitmid = isset($row["bsitmid"]) ? $row["bsitmid"] : 0;
    $bsitmsubctgid = isset($row["bsitmsubctgid"]) ? $row["bsitmsubctgid"] : 0;
    $bsitmctgid = isset($row["bsitmctgid"]) ? $row["bsitmctgid"] : 0;
    $bsmetaltype = isset($row["bsmetaltype"]) ? $row["bsmetaltype"] : '';
    $itgstper = isset($row["itgstper"]) ? $row["itgstper"] : 0;
    $bscarat = isset($row["bscarat"]) ? $row["bscarat"] : 0;
    $bsdiawt = isset($row["bsdiawt"]) ? $row["bsdiawt"] : 0;

    $Qty = 1; // Default quantity, change this if dynamic

    // Check if rate and nettWt are non-zero before performing multiplication
    $metval = 0;
    if ($rate > 0 && $nettWt > 0) {
        $metval = $nettWt * $rate;
    } else {
        $metval = 0;
        error_log("Invalid rate or nettWt: rate = $rate, nettWt = $nettWt");
    }
// echo json_encode($metval);
    // Calculate MC value based on type and calcon
    if ($makingchrgtype == 'V' && $makingcalcon == 'NW') {
        $mcval = $nettWt * $makingPer;
    } elseif ($makingchrgtype == 'P' && $makingcalcon == 'NW') {
        if ($metval > 0) {
            $mcval = ($metval * $makingPer) / 100;
        } else {
            $mcval = 0;
        }
    } elseif ($makingchrgtype == 'V' && $makingcalcon == 'PC') {
        $mcval = $makingPer * $Qty;
    } else {
        $mcval = 0;
    }

    // Calculate Wastage value based on type and calcon
    if ($wstchrgtype == 'V' && $wstcalcon == 'NW') {
        $wstval = $nettWt * $wstPer;
    } elseif ($wstchrgtype == 'P' && $wstcalcon == 'NW') {
        if ($metval > 0) {
            $wstval = ($metval * $wstPer) / 100;
            // echo json_encode($wstval);
        } else {
            $wstval = 0;
        }
    } elseif ($wstchrgtype == 'V' && $wstcalcon == 'PC') {
        $wstval = $wstPer * $Qty;
    } else {
        $wstval = 0;
    }

    // Final amount calculations
    $grossamt = $metval + $wstval + $mcval + $hallMark + $stoneVal - $offerDis;
    

    // Avoid tax calculation if grossamt is negative
    if ($grossamt > 0) {       
        if($itgstper == "GST3") {
            $taxval = ($grossamt * 3) / 100;
        } else {
            $taxval = ($grossamt * 18) / 100;
        }
    } else {
        $taxval = 0;
    }

    $finalamt = $grossamt + $taxval; // Removed .toFixed(2)

    // Round off the final amount if needed
    $finalamt = round($finalamt, 2);

    // Check if all values are properly calculated and populate the final response
    $trn_no[] = [
        "barcode" => isset($row["barcode"]) ? $row["barcode"] : '',
        "name" => isset($row["name"]) ? $row["name"] : 'Unknown',
        "nettWt" => $nettWt,
        "rate" => $rate,
        "offerDis" => $offerDis,
        "wstper" => $wstPer,
        "stoneVal" => $stoneVal,
        "mcval" => $mcval,
        "metval" => $metval,
        "wstval" => $wstval,
        "hmcval" => $hallMark,
        "mcper" => $makingPer,
        "makingchrgtype" => $makingchrgtype,
        "makingcalcon" => $makingcalcon,
        "stoneWt" => isset($row["bsstonewt"]) ? $row["bsstonewt"] : 0,
        "grossamt" => $grossamt,
        "taxVal" => $taxval,
        "finalAmt" => $finalamt,
        "bsitmid" => $bsitmid,
        "bsitmsubctgid" => $bsitmsubctgid,
        "bsitmctgid" => $bsitmctgid,
        "bsmetaltype" => $bsmetaltype,
        "bscarat" => $bscarat,
        "bsdiawt" => $bsdiawt,
        "itgstper" => $itgstper
    ];
}

if (empty($trn_no)) {
    echo json_encode([
        "status" => "error",
        "message" => "Nill Stock."
    ]);
    exit();
}

echo json_encode([
    "status" => "success",
    "data" => $trn_no
]);