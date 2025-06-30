<?php
// Include your database connection here
include '../inc/config.php'; // Adjust as necessary

if (isset($_POST['Carot'])) {
    $Carot = $_POST['Carot'] . 'KT';

    // Prepare your SQL statement
    $stmt = $pdo->prepare("SELECT price FROM gold_price WHERE type = :carot");
    $stmt->bindParam(':carot', $Carot);

    // Execute the statement
    $stmt->execute();

    // Fetch the result
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Assuming 'price' is the column you want
        echo $result['price'];
    } else {
        echo 'No data found';
    }
} else {
    echo 'Carot not provided';
}
?>
