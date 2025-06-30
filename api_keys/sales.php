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
 * Execute SQL query and return results
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
                $sql = "SELECT cus.CustName, cus.CustMobileNo, cus.CustCd, cus.OTPVerified, CONVERT(NVARCHAR(50),last_call.cltime,106) AS Lst_Cltime,CASE WHEN last_call.cltime IS NOT NULL THEN 'Y' ELSE 'N' END AS CalledToday
                FROM MstCRM crm
                INNER JOIN MstCustomer cus ON cus.CustCd = crm.crmcustcd
                LEFT JOIN (SELECT clcustmobile,MAX(cltime) AS cltime FROM TrnCustLead  GROUP BY clcustmobile) last_call ON last_call.clcustmobile = crm.crmcustmobile
                WHERE crm.crmempid = ? AND crm.crmwrkunt = ? AND cus.CustWrkUnt = ?
                ORDER BY cus.CustCd";
                $response['data'] = executeQuery($conn, $sql, [$emp_code, $empwrkunit, $empwrkunit]);
                break;

            case 'bill_value':
                $sql = "SELECT cus.CustCd,cus.CustName, cus.CustMobileNo, SUM(crm.bnetamt) AS bill_value, cus.OTPVerified ,CASE WHEN last_call.cltime IS NOT NULL THEN 'Y' ELSE 'N' END AS CalledToday
                        FROM TrnBillHdr crm
                        INNER JOIN MstCustomer cus ON cus.CustCd = crm.bcustcd
                        INNER JOIN MstCRM mas ON mas.crmcustcd = cus.CustCd
                        LEFT JOIN (SELECT clcustmobile,MAX(cltime) AS cltime FROM TrnCustLead  GROUP BY clcustmobile) last_call ON last_call.clcustmobile = mas.crmcustmobile AND CAST(last_call.cltime AS DATE) = CAST(GETDATE() AS DATE)
                        WHERE mas.crmempid = ? AND crm.bempwrkunt = ? AND cus.CustWrkUnt = ?
                        GROUP BY cus.CustCd, cus.CustName, cus.CustMobileNo, cus.OTPVerified, last_call.cltime
                        ORDER BY bill_value DESC";
                $response['data'] = executeQuery($conn, $sql, [$emp_code, $empwrkunit, $empwrkunit]);
                break;

            case 'bday':
                $sql = "SELECT cus.CustName, cus.CustMobileNo, CONVERT(varchar(50), cus.CustDOB, 106) AS CustDOB, cus.OTPVerified,CASE WHEN last_call.cltime IS NOT NULL THEN 'Y' ELSE 'N' END AS CalledToday
                        FROM MstCRM crm
                        INNER JOIN MstCustomer cus ON cus.CustCd = crm.crmcustcd
                        LEFT JOIN (SELECT clcustmobile,MAX(cltime) AS cltime FROM TrnCustLead  GROUP BY clcustmobile) last_call ON last_call.clcustmobile = crm.crmcustmobile
                        WHERE crm.crmempid = ? AND cus.CustDOB IS NOT NULL AND TRY_CAST(CONCAT(YEAR(GETDATE()), FORMAT(cus.CustDOB, 'MMdd')) AS date) BETWEEN CAST(GETDATE() AS date) AND DATEADD(DAY, 6, CAST(GETDATE() AS date))
                        ORDER BY MONTH(cus.CustDOB), DAY(cus.CustDOB);
                        ";
                $response['data'] = executeQuery($conn, $sql, [$emp_code]);
                break;

            case 'visit_cnt':
                $sql = "SELECT cus.CustName, cus.CustMobileNo, COUNT(vis.btrndate) as visit_count,crm.crmempid, cus.OTPVerified,CASE WHEN led.cltime IS NOT NULL THEN 'Y' ELSE 'N' END AS CalledToday
                        FROM MstCRM crm
                        INNER JOIN MstCustomer cus ON cus.CustCd = crm.crmcustcd
                        INNER JOIN TrnBillHdr vis ON vis.bcustcd = cus.CustCd
                        LEFT JOIN TrnCustLead led ON led.clcustmobile = crm.crmcustmobile AND CAST(led.cltime AS DATE) = CAST(GETDATE() AS DATE)
                        WHERE crm.crmempid = ? and cus.CustWrkUnt = ? AND vis.bwrkunt = ?
                        GROUP BY cus.CustName, cus.CustMobileNo,crm.crmempid, cus.OTPVerified, led.cltime
                        ORDER BY visit_count DESC";
                $response['data'] = executeQuery($conn, $sql, [$emp_code, $empwrkunit, $empwrkunit]);
                break;

            case 'lst_visit':
                $sql = "SELECT cus.CustName, cus.CustMobileNo, FORMAT(MAX(vis.btrndate), 'dd MMM yyyy') AS last_visit,
                        DATEDIFF(DAY, MAX(CAST(vis.btrndate AS DATE)), CAST(GETDATE() AS DATE)) AS Cnt,
                        cus.OTPVerified,CASE WHEN led.cltime IS NOT NULL THEN 'Y' ELSE 'N' END AS CalledToday
                        FROM MstCRM crm
                        INNER JOIN MstCustomer cus ON cus.CustCd = crm.crmcustcd
                        INNER JOIN TrnBillHdr vis ON vis.bcustcd = cus.CustCd
                        LEFT JOIN TrnCustLead led ON led.clcustmobile = crm.crmcustmobile AND CAST(led.cltime AS DATE) = CAST(GETDATE() AS DATE)
                        WHERE crm.crmempid = ? AND cus.CustWrkUnt = ? AND vis.bwrkunt = ?
                        GROUP BY cus.CustName, cus.CustMobileNo, cus.OTPVerified, led.cltime
                        ORDER BY Cnt DESC";
                $response['data'] = executeQuery($conn, $sql, [$emp_code, $empwrkunit, $empwrkunit]);
                break;

            case 'lead':
                header('Content-Type: application/json');
                
                try {
                    // Get and validate leadType parameter
                    $leadType = $_POST['leadType'] ?? '';
                    
                    // Build your SQL query based on leadType
                    if ($leadType != '') {
                        // Get filtered leads
                        $sql = "SELECT CUS.CustName, CUS.CustMobileNo, TRN.clleadtype AS LeadType 
                                FROM TrnCustLead TRN
                                INNER JOIN mstCustomer CUS ON CUS.CustCd = TRN.clcustcd
                                WHERE TRN.clwrkunt = ? AND TRN.clleadtype = ? AND TRN.clempid = ? AND CUS.CustWrkUnt = ?
                                ORDER BY cltime DESC";
                        $params = [$empwrkunit, $leadType, $emp_code, $empwrkunit];
                    } else {
                        // Get all leads
                        $sql = "SELECT CUS.CustName, CUS.CustMobileNo, TRN.clleadtype AS LeadType 
                                FROM TrnCustLead TRN
                                INNER JOIN mstCustomer CUS ON CUS.CustCd = TRN.clcustcd
                                WHERE TRN.clwrkunt = ? AND TRN.clempid = ? AND CUS.CustWrkUnt = ?
                                ORDER BY cltime DESC";
                        $params = [$empwrkunit, $emp_code, $empwrkunit];
                    }
                    
                    $stmt = sqlsrv_query($conn, $sql, $params);
                    if ($stmt === false) {
                        throw new Exception("Database error: " . print_r(sqlsrv_errors(), true));
                    }
                    
                    $data = [];
                    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                        $data[] = [
                            'CustName' => $row['CustName'],
                            'CustMobileNo' => $row['CustMobileNo'],
                            'LeadType' => $row['LeadType'] ?? ''
                        ];
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $data
                    ]);
                exit;
                
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                exit;
            }
            break;

            case 'bill_details':
            try {
                // Use $_REQUEST to support both GET and POST (or stick with $_GET/$_POST)
                $mobileNo = $_REQUEST['mobileNo'] ?? '';

                if (empty($mobileNo)) {
                    throw new Exception("Mobile number is required");
                }

                $sql = "SELECT TRN.btrndate AS bill_date,TRN.bstrtrnno AS bill_number,TRN.bnetamt AS amount,CUS.CustName AS customer_name
                        FROM MstCRM CRM
                        INNER JOIN MstCustomer CUS ON CUS.CustMobileNo = CRM.crmcustmobile
                        INNER JOIN TrnBillHdr TRN ON TRN.bcustcd = CUS.CustCd
                        WHERE CRM.crmcustmobile = ? AND TRN.bwrkunt = ? AND CUS.CustWrkUnt= ?
                        ORDER BY TRN.btrndate DESC";

                $params = [$mobileNo, $empwrkunit, $empwrkunit];
                $stmt = sqlsrv_query($conn, $sql, $params);

                if ($stmt === false) {
                    throw new Exception("Database error: " . print_r(sqlsrv_errors(), true));
                }

                $bills = [];
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    if (!empty($row['bill_date'])) {
                        $row['bill_date'] = $row['bill_date']->format('Y-m-d');
                    }
                    $bills[] = $row;
                }

                echo json_encode([
                    'success' => true,
                    'data' => $bills,
                    'customer' => $bills[0]['customer_name'] ?? null
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            exit; // Make sure nothing else runs
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
                    (string) $emp_code,
                    (string) $_POST['CustCd'] ?? '',
                    (string) $_POST['CustMobileNo'] ?? '',
                    $empwrkunit,
                    (string) $_POST['remarks'] ?? ''
                ];

                $stmt = sqlsrv_query($conn, $sql, $params);
                if ($stmt === false) {
                    throw new Exception("Failed to log call: " . print_r(sqlsrv_errors(), true));
                }

                $response = [
                    'success' => true,
                    'show_lead' => true
                ];
                break;

            case 'gold_weight':
                header('Content-Type: application/json');
                
                try {
                    // Validate inputs
                    if (empty($_POST['CustMobileNo'])) {
                        throw new Exception("Customer mobile number is required");
                    }

                    // Debugging - log to error log instead of echoing
                    error_log("Gold Weight Data: " . print_r($_POST, true));

                    $sql = "UPDATE TrnCustLead SET clleadtype = ?, clrangewt = ?, clrangeval = ?
                            WHERE clcustcd = ? AND clcustmobile = ? AND cltime = (SELECT MAX(cltime) FROM TrnCustLead WHERE clcustcd = ? AND clcustmobile = ? AND clwrkunt = ?)";

                    $params = [
                        $_POST['lead_type'] ?? 'S', // Default to 'S' if not provided
                        $_POST['range_weight'] ?? 0,
                        $_POST['range_value'] ?? 0,
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

                    echo json_encode(['success' => true]);
                    exit;
                    
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                    exit;
                }
            break;

            case 'chit_scheme':
                header('Content-Type: application/json');

                try {
                    // Validate inputs
                    if (empty($_POST['CustMobileNo'])) {
                        throw new Exception("Customer mobile number is required");
                    }

                    $sql = "UPDATE TrnCustLead SET clleadtype = ?, clchittype = ?, cljoinval = ?
                            WHERE clcustcd = ? AND clcustmobile = ? AND cltime = (SELECT MAX(cltime) FROM TrnCustLead WHERE clcustcd = ? AND clcustmobile = ? AND clwrkunt = ?)";

                    $params = [
                        $_POST['lead_type'],
                        $_POST['chit_type'],
                        $_POST['join_value'],
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
                header('Content-Type: application/json');

                try {
                    // Validate inputs
                    if (empty($_POST['CustMobileNo'])) {
                        throw new Exception("Customer mobile number is required");
                    }

                    $sql = "UPDATE TrnCustLead SET clleadtype = ?, clpurpose = ?
                            WHERE clcustcd = ? AND clcustmobile = ? AND cltime = (SELECT MAX(cltime) FROM TrnCustLead WHERE clcustcd = ? AND clcustmobile = ? AND clwrkunt = ?)";

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
                header('Content-Type: application/json');
                
                try {
                    // Debugging - log instead of echo
                    error_log("Lead Time Received: " . $_POST['lead_time']);
                    
                    if (empty($_POST['CustMobileNo'])) {
                        throw new Exception("Mobile number is required");
                    }

                    // Fix SQL query - removed duplicate parameters
                    $sql = "UPDATE TrnCustLead SET clleadtime = CONVERT(datetime, ?)
                            WHERE clcustcd = ? 
                            AND clcustmobile = ? 
                            AND cltime = (
                                SELECT MAX(cltime) 
                                FROM TrnCustLead 
                                WHERE clcustcd = ? 
                                AND clcustmobile = ? 
                                AND clwrkunt = ?
                            )";

                    // Fixed parameters - removed duplicates and added proper ordering
                    $params = [
                        $_POST['lead_time'],  // Date string
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

                    echo json_encode(['success' => true]);
                    exit;
                    
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                    exit;
                }
            break;

            case 'update_lead':
                if (empty($_POST['remarks'])) {
                    throw new Exception("Remarks are required");
                }

                $sql = "UPDATE TrnCustLead 
                        SET clremarks = ?
                        WHERE crm.crmcustcd = ? AND clcustmobile = ?
                        AND call_time = (
                            SELECT MAX(call_time) 
                            FROM TrnCustLead 
                            WHERE crm.crmcustcd = ? AND clcustmobile = ?
                        )";
                $params = [
                    $_POST['remarks'],
                    $emp_code,
                    $_POST['CustMobileNo'],
                    $emp_code,
                    $_POST['CustMobileNo']
                ];

                $stmt = sqlsrv_query($conn, $sql, $params);
                if ($stmt === false) {
                    throw new Exception("Failed to update lead: " . print_r(sqlsrv_errors(), true));
                }

                $response = [
                    'success' => true,
                    'updated' => sqlsrv_rows_affected($stmt)
                ];
                break;

            case 'inactive':
                if (empty($_POST['CustMobileNo'])) {
                    throw new Exception("Customer mobile number is required");
                }

                $sql = "UPDATE MstCRM SET custact = 'N'
                        WHERE crmempid = ? AND crmwrkunt = ? AND crmcustcd = ? AND crmcustmobile = ?";

                $params = [
                    $emp_code,
                    $empwrkunit,
                    $_POST['CustCd'],
                    $_POST['CustMobileNo']
                ];

                $stmt = sqlsrv_query($conn, $sql, $params);
                if ($stmt === false) {
                    throw new Exception("Failed to mark lead as inactive: " . print_r(sqlsrv_errors(), true));
                }

                $response = ['success' => true];
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