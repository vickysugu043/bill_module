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

/**
 * Execute SQL query AND return results
 */
function executeQuery($conn, $sql, $params = [])
{
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        throw new Exception("Query execution failed: " . print_r(sqlsrv_errors(), true));
    }

    $results = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $results[] = $row;
    }

    return $results;
}

/**
 * Handle GET requests
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    try {
        $action = $_GET['action'];
        $response = [];

        switch ($action) {
            case 'customer':
                $sql = "SELECT DISTINCT CustMobileNo,CustCd, CustName, sj.sjjoinempid, sj.sjjoinempwrkunt,CASE WHEN led.cltime IS NOT NULL THEN 'Y' ELSE 'N' END AS CalledToday 
                FROM TrnSchJoin sj
                INNER JOIN MstCustomer cus ON cus.CustCd = sj.sjcusid
                LEFT JOIN TrnCustLead led ON led.clcustmobile = cus.CustMobileNo AND CAST(led.cltime AS DATE) = CAST(GETDATE() AS DATE)
                WHERE sjwrkunt = ? AND sjjoinempwrkunt = ? AND sjjoinempid = ? AND schclosed = 'N'";
                $response['data'] = executeQuery($conn, $sql, [(int)$empwrkunit, (int)$empwrkunit, $emp_code]);
                break;

            case 'schemes':
                $sql = "SELECT DISTINCT CustMobileNo,CustCd, CustName, sj.sjjoinempid, sj.sjjoinempwrkunt FROM TrnSchJoin sj
                INNER JOIN MstCustomer cus ON cus.CustCd = sj.sjcusid
                WHERE sjwrkunt = ? AND sjjoinempwrkunt = ? AND sjjoinempid = ? AND schclosed = 'N'";
                $response['data'] = executeQuery($conn, $sql, [$empwrkunit, $empwrkunit, $emp_code]);
                break;

            case 'pendings':
                $sql = "SELECT  pa.CustName ,pa.CustMobileNo , pa.smjointrnno,pa.sjtrndate,PaidIns,PendIns,LastPaidDate FROM  (SELECT CustName ,CustMobileNo , smjointrnno,smjointrnyr , sjtrndate ,smwrkunt, count(*) AS PaidIns,
                MAX(sminsmonth) AS LastPaidDate FROM trnschEntMonWise EMW
                LEFT JOIN TrnSchJoin TSJ ON TSJ.sjwrkunt = EMW.smwrkunt AND TSJ.sjtrnno = EMW.smjointrnno AND TSJ.sjtrnyr = EMW.smjointrnyr 
                LEFT JOIN  TrnNewScheme NSCH ON NSCH.nswrkunt = TSJ.sjwrkunt AND NSCH.nsschserno = TSJ.sjschserno AND NSCH.nsschseryr = TSJ.sjschseryr 
                LEFT JOIN MstScheme SCH ON SCH.schwrkunt = NSCH.nswrkunt AND SCH.schcd = NSCH.nsschcd 
                INNER JOIN MstCustomer Cus on cus.CustCd = tsj.sjcusid AND cus.CustWrkUnt = tsj.sjcustwrkunt 
                WHERE smwrkunt = ? AND smpaidsts ='Y' AND schclosed ='N' AND sjjoinempwrkunt = ? AND sjjoinempid = ?
                GROUP BY CustName ,CustMobileNo  ,smjointrnno,smjointrnyr,sjtrndate,smwrkunt) pa
                LEFT JOIN (SELECT CustName ,CustMobileNo , smjointrnno,smjointrnyr,sjtrndate ,smwrkunt,count(*) AS PendIns FROM trnschEntMonWise EMW
                LEFT JOIN TrnSchJoin TSJ ON TSJ.sjwrkunt = EMW.smwrkunt AND TSJ.sjtrnno = EMW.smjointrnno AND TSJ.sjtrnyr = EMW.smjointrnyr 
                LEFT JOIN  TrnNewScheme NSCH ON NSCH.nswrkunt = TSJ.sjwrkunt AND NSCH.nsschserno = TSJ.sjschserno AND NSCH.nsschseryr = TSJ.sjschseryr 
                LEFT JOIN MstScheme SCH ON SCH.schwrkunt = NSCH.nswrkunt AND SCH.schcd = NSCH.nsschcd 
                INNER JOIN MstCustomer Cus on cus.CustCd = tsj.sjcusid AND cus.CustWrkUnt = tsj.sjcustwrkunt 
                WHERE smwrkunt = ? AND smpaidsts ='N' AND schclosed ='N' AND sjjoinempwrkunt = ? AND sjjoinempid = ?
                GROUP BY CustName ,CustMobileNo  ,smjointrnno,smjointrnyr,sjtrndate,smwrkunt) Pe on pe.CustName = pa.CustName AND pa.CustMobileNo =pe.CustMobileNo AND pa.smjointrnno =pe.smjointrnno AND pa.smjointrnyr=pe.smjointrnyr 
                AND pa.sjtrndate = pe.sjtrndate AND pa.smwrkunt = pe.smwrkunt ";
                $response['data'] = executeQuery($conn, $sql, [$empwrkunit, $empwrkunit, $emp_code, $empwrkunit, $empwrkunit, $emp_code]);
                break;

            case 'defaulters':
                $sql = "SELECT CustName ,CustMobileNo , smjointrnno,smjointrnyr , sjtrndate  FROM trnschEntMonWise EMW
                LEFT JOIN TrnSchJoin TSJ ON TSJ.sjwrkunt = EMW.smwrkunt AND TSJ.sjtrnno = EMW.smjointrnno AND TSJ.sjtrnyr = EMW.smjointrnyr 
                LEFT JOIN  TrnNewScheme NSCH ON NSCH.nswrkunt = TSJ.sjwrkunt AND NSCH.nsschserno = TSJ.sjschserno AND NSCH.nsschseryr = TSJ.sjschseryr 
                LEFT JOIN MstScheme SCH ON SCH.schwrkunt = NSCH.nswrkunt AND SCH.schcd = NSCH.nsschcd 
                INNER JOIN MstCustomer Cus on cus.CustCd = tsj.sjcusid and cus.CustWrkUnt = tsj.sjcustwrkunt 
                WHERE smwrkunt = ? AND smpaidsts ='N' 
                AND schclosed ='N'
                AND sjjoinempwrkunt = ?
                AND sjjoinempid = ?
                GROUP BY CustName ,CustMobileNo  ,smjointrnno,smjointrnyr,sjtrndate
                HAVING count(*)>2";
                $response['data'] = executeQuery($conn, $sql, [$empwrkunit, $empwrkunit, $emp_code]);
                break;

            case 'active':
                $sql = "SELECT cus.CustName ,cus.CustMobileNo , EMW.smjointrnno,EMW.smjointrnyr , sjtrndate   FROM trnschEntMonWise EMW
                LEFT JOIN TrnSchJoin TSJ ON TSJ.sjwrkunt = EMW.smwrkunt AND TSJ.sjtrnno = EMW.smjointrnno AND TSJ.sjtrnyr = EMW.smjointrnyr 
                LEFT JOIN  TrnNewScheme NSCH ON NSCH.nswrkunt = TSJ.sjwrkunt AND NSCH.nsschserno = TSJ.sjschserno AND NSCH.nsschseryr = TSJ.sjschseryr 
                LEFT JOIN MstScheme SCH ON SCH.schwrkunt = NSCH.nswrkunt AND SCH.schcd = NSCH.nsschcd 
                INNER JOIN MstCustomer Cus on cus.CustCd = tsj.sjcusid and cus.CustWrkUnt = tsj.sjcustwrkunt 
                WHERE EMW.smwrkunt = ? 
                AND smpaidsts ='N'
                AND schclosed ='N'
                AND sjjoinempwrkunt = ?
                AND sjjoinempid = ?
                AND sminsmonth = convert(date,DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1))
                GROUP BY cus.CustName ,cus.CustMobileNo , EMW.smjointrnno,EMW.smjointrnyr,sjtrndate";
                $response['data'] = executeQuery($conn, $sql, [$empwrkunit, $empwrkunit, $emp_code]);
                break;

            case 'scheme_details':
                $mobileNo = $_GET['mobileNo'] ?? '';
                if (empty($mobileNo)) {
                    throw new Exception("Mobile number is required");
                }

                $sql = "SELECT  pa.CustName ,pa.CustMobileNo , pa.smjointrnno,pa.sjtrndate,ISNULL(PendIns,0) AS PendIns,PendIns,LastPaidDate FROM  (SELECT CustName ,CustMobileNo , smjointrnno,smjointrnyr , sjtrndate ,smwrkunt, count(*) AS PaidIns,
                MAX(sminsmonth) AS LastPaidDate FROM trnschEntMonWise EMW
                LEFT JOIN TrnSchJoin TSJ ON TSJ.sjwrkunt = EMW.smwrkunt AND TSJ.sjtrnno = EMW.smjointrnno AND TSJ.sjtrnyr = EMW.smjointrnyr 
                LEFT JOIN  TrnNewScheme NSCH ON NSCH.nswrkunt = TSJ.sjwrkunt AND NSCH.nsschserno = TSJ.sjschserno AND NSCH.nsschseryr = TSJ.sjschseryr 
                LEFT JOIN MstScheme SCH ON SCH.schwrkunt = NSCH.nswrkunt AND SCH.schcd = NSCH.nsschcd 
                INNER JOIN MstCustomer Cus on cus.CustCd = tsj.sjcusid AND cus.CustWrkUnt = tsj.sjcustwrkunt 
                WHERE smwrkunt = ? AND smpaidsts ='Y' AND schclosed ='N' AND sjjoinempwrkunt = ? AND sjjoinempid = ? AND CUS.CustMobileNo = ?
                GROUP BY CustName ,CustMobileNo  ,smjointrnno,smjointrnyr,sjtrndate,smwrkunt) pa
                LEFT JOIN (SELECT CustName ,CustMobileNo , smjointrnno,smjointrnyr,sjtrndate ,smwrkunt,count(*) AS PendIns FROM trnschEntMonWise EMW
                LEFT JOIN TrnSchJoin TSJ ON TSJ.sjwrkunt = EMW.smwrkunt AND TSJ.sjtrnno = EMW.smjointrnno AND TSJ.sjtrnyr = EMW.smjointrnyr 
                LEFT JOIN  TrnNewScheme NSCH ON NSCH.nswrkunt = TSJ.sjwrkunt AND NSCH.nsschserno = TSJ.sjschserno AND NSCH.nsschseryr = TSJ.sjschseryr 
                LEFT JOIN MstScheme SCH ON SCH.schwrkunt = NSCH.nswrkunt AND SCH.schcd = NSCH.nsschcd 
                INNER JOIN MstCustomer Cus on cus.CustCd = tsj.sjcusid AND cus.CustWrkUnt = tsj.sjcustwrkunt 
                WHERE smwrkunt = ? AND smpaidsts ='N' AND schclosed ='N' AND sjjoinempwrkunt = ? AND sjjoinempid = ? AND CUS.CustMobileNo = ?
                GROUP BY CustName ,CustMobileNo  ,smjointrnno,smjointrnyr,sjtrndate,smwrkunt) Pe on pe.CustName = pa.CustName AND pa.CustMobileNo =pe.CustMobileNo AND pa.smjointrnno =pe.smjointrnno AND pa.smjointrnyr=pe.smjointrnyr 
                AND pa.sjtrndate = pe.sjtrndate AND pa.smwrkunt = pe.smwrkunt";

                $results = executeQuery($conn, $sql, [$empwrkunit, $empwrkunit, $emp_code, $mobileNo, $empwrkunit, $empwrkunit, $emp_code, $mobileNo]);
                
                // Format dates
                foreach ($results AS &$row) {
                    if ($row['sjtrndate'] instanceof DateTime) {
                        $row['sjtrndate'] = $row['sjtrndate']->format('Y-m-d');
                    }
                    if ($row['LastPaidDate'] instanceof DateTime) {
                        $row['LastPaidDate'] = $row['LastPaidDate']->format('Y-m-d');
                    }
                    if($row['smjointrnno'] === null) {
                        $row['smjointrnno'] = '';
                    }
                }
                
                $response = [
                    'success' => true,
                    'data' => $results,
                    'customer' => $results[0]['customer_name'] ?? null
                ];
                echo json_encode($response);
                exit();
                break;

            default:
                throw new Exception("Invalid action specified");
        }

        $response['success'] = true;
        echo json_encode($response);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

/**
 * Handle POST requests
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $action = $_POST['action'];
        $response = [];

        switch ($action) {
            case 'call_log':
                if (empty($_POST['CustMobileNo'])) {
                    throw new Exception("Mobile number is required");
                }

                $sql = "INSERT INTO TrnCustLead (clempid, clcustcd, clcustmobile, clwrkunt, cltime, clremarks) 
                        VALUES (?, ?, ?, ?, GETDATE(), ?)";

                $params = [
                    $emp_code,
                    $_POST['CustCd'] ?? '',
                    $_POST['CustMobileNo'],
                    $empwrkunit,
                    $_POST['remarks'] ?? ''
                ];

                $stmt = sqlsrv_query($conn, $sql, $params);
                if ($stmt === false) {
                    throw new Exception("Failed to log call: " . print_r(sqlsrv_errors(), true));
                }

                $response = ['success' => true, 'show_lead' => true];
                break;

            case 'gold_weight':
                try {
                    $rangeWeight = floatval($_POST['range_weight'] ?? 0);
                    $rangeValue = floatval($_POST['range_value'] ?? 0);
                    
                    if (!is_numeric($rangeWeight) || !is_numeric($rangeValue)) {
                        throw new Exception("Weight AND value must be numeric");
                    }

                    $sql = "UPDATE TrnCustLead SET clleadtype = ?, clrangewt = CAST(? AS DECIMAL(18,2)), clrangeval = CAST(? AS DECIMAL(18,2))
                            WHERE clcustcd = ? AND clcustmobile = ? AND cltime = (SELECT MAX(cltime) FROM TrnCustLead WHERE clcustcd = ? AND clcustmobile = ? AND clwrkunt = ?)";

                    $params = [
                        $_POST['lead_type'] ?? 'S',
                        $rangeWeight,
                        $rangeValue,
                        $_POST['CustCd'] ?? '',
                        $_POST['CustMobileNo'],
                        $_POST['CustCd'] ?? '',
                        $_POST['CustMobileNo'],
                        $empwrkunit
                    ];

                    $stmt = sqlsrv_query($conn, $sql, $params);
                    if ($stmt === false) {
                        throw new Exception("Database error: " . print_r(sqlsrv_errors(), true));
                    }

                    $response = ['success' => true];
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                    exit;
                }
                break;

            case 'chit_scheme':
                try {
                    $joinValue = floatval($_POST['join_value'] ?? 0);
                    if (!is_numeric($joinValue)) {
                        throw new Exception("Join value must be numeric");
                    }

                    $sql = "UPDATE TrnCustLead 
                            SET clleadtype = ?, 
                                clchittype = ?, 
                                cljoinval = CAST(? AS DECIMAL(18,2))
                            WHERE clcustcd = ? 
                            AND clcustmobile = ? 
                            AND cltime = (
                                SELECT MAX(cltime) 
                                FROM TrnCustLead 
                                WHERE clcustcd = ? 
                                AND clcustmobile = ? 
                                AND clwrkunt = ?
                            )";

                    $params = [
                        $_POST['lead_type'],
                        $_POST['chit_type'],
                        $joinValue,
                        $_POST['CustCd'],
                        $_POST['CustMobileNo'],
                        $_POST['CustCd'] ?? '',
                        $_POST['CustMobileNo'] ?? '',
                        $empwrkunit
                    ];

                    $stmt = sqlsrv_query($conn, $sql, $params);
                    if ($stmt === false) {
                        throw new Exception("Failed to save chit scheme: " . print_r(sqlsrv_errors(), true));
                    }

                    $response = ['success' => true];
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                    exit;
                }
                break;

            case 'visit_purpose':
                try {
                    $sql = "UPDATE TrnCustLead 
                            SET clleadtype = ?, 
                                clpurpose = ?
                            WHERE clcustcd = ? 
                            AND clcustmobile = ? 
                            AND cltime = (
                                SELECT MAX(cltime) 
                                FROM TrnCustLead 
                                WHERE clcustcd = ? 
                                AND clcustmobile = ? 
                                AND clwrkunt = ?
                            )";

                    $params = [
                        $_POST['lead_type'],
                        $_POST['visit_purpose'],
                        $_POST['CustCd'],
                        $_POST['CustMobileNo'],
                        $_POST['CustCd'] ?? '',
                        $_POST['CustMobileNo'] ?? '',
                        $empwrkunit
                    ];

                    $stmt = sqlsrv_query($conn, $sql, $params);
                    if ($stmt === false) {
                        throw new Exception("Failed to save visit purpose: " . print_r(sqlsrv_errors(), true));
                    }

                    $response = ['success' => true];
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                    exit;
                }
                break;

            case 'save_lead':
                try {
                    if (empty($_POST['lead_time'])) {
                        throw new Exception("Lead time is required");
                    }

                    $sql = "UPDATE TrnCustLead 
                            SET clleadtime = CONVERT(datetime, ?)
                            WHERE clcustcd = ? 
                            AND clcustmobile = ? 
                            AND cltime = (
                                SELECT MAX(cltime) 
                                FROM TrnCustLead 
                                WHERE clcustcd = ? 
                                AND clcustmobile = ? 
                                AND clwrkunt = ?
                            )";

                    $params = [
                        $_POST['lead_time'],
                        $_POST['CustCd'] ?? '',
                        $_POST['CustMobileNo'],
                        $_POST['CustCd'] ?? '',
                        $_POST['CustMobileNo'],
                        $empwrkunit
                    ];

                    $stmt = sqlsrv_query($conn, $sql, $params);
                    if ($stmt === false) {
                        throw new Exception("Database error: " . print_r(sqlsrv_errors(), true));
                    }

                    $response = ['success' => true];
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                    exit;
                }
                break;

            case 'inactive':
                try {
                    $sql = "UPDATE MstCRM 
                            SET custact = 'N'
                            WHERE crmempid = ? 
                            AND crmwrkunt = ? 
                            AND crmcustcd = ? 
                            AND crmcustmobile = ?";

                    $params = [
                        $emp_code,
                        $empwrkunit,
                        $_POST['CustCd'],
                        $_POST['CustMobileNo']
                    ];

                    $stmt = sqlsrv_query($conn, $sql, $params);
                    if ($stmt === false) {
                        throw new Exception("Failed to mark lead AS inactive: " . print_r(sqlsrv_errors(), true));
                    }

                    $response = ['success' => true];
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                    exit;
                }
                break;

            default:
                throw new Exception("Invalid action specified");
        }

        echo json_encode($response);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

// Close connection
sqlsrv_close($conn);
?>