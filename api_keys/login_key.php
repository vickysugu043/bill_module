<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$UserName = isset($_GET['username']) ? trim($_GET['username']) : '';
$InputPassword = isset($_GET['password']) ? trim($_GET['password']) : '';

if (empty($UserName) || empty($InputPassword)) {
    echo json_encode([
        "status" => "error",
        "message" => "Username and Password are required!"
    ]);
    exit();
}

$databaseConnections = [
    "server" => "10.0.205.2\\SQL2019",
    "database" => "DAIVELCUMBUMTEST",
    "user" => "sa",
    "password" => "Admin@1234"
];

$connectionInfo = [
    "Database" => $databaseConnections['database'],
    "UID" => $databaseConnections['user'],
    "PWD" => $databaseConnections['password']
];
$conn = sqlsrv_connect($databaseConnections['server'], $connectionInfo);

if ($conn === false) {
    echo json_encode([
        "status" => "error",
        "message" => "Connection failed!",
        "details" => sqlsrv_errors()
    ]);
    exit();
}

$sql = "SELECT UserName, Password FROM UserMaster WHERE UserName = ?";
$params = [$UserName];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode([
        "status" => "error",
        "message" => "Query execution failed!",
        "details" => sqlsrv_errors()
    ]);
    exit();
}

$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if ($user) {
    $encryptedPassword = $user['Password'];

    // Call decrypt.php
    $payload = json_encode(['encrypted' => $encryptedPassword]);

    $ch = curl_init('http://localhost/decrypt.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);

    if ($response === false) {
        $curlError = curl_error($ch);
        curl_close($ch);
        echo json_encode([
            "status" => "error",
            "message" => "Decryption API call failed!",
            "details" => $curlError
        ]);
        exit();
    }

    curl_close($ch);
    $decryptedPassword = trim($response);

    if (empty($decryptedPassword)) {
        echo json_encode([
            "status" => "error",
            "message" => "Decryption failed!",
            "redirect" => "same"
        ]);
        exit();
    }

    if ($decryptedPassword === $InputPassword) {
        echo json_encode([
            "status" => "success",
            "message" => "Login successful!",
            "data" => $user,
            "redirect" => "billing.html"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid password!",
            "redirect" => "same"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "User not found!",
        "redirect" => "same"
    ]);
}
?>
