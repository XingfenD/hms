<?php
include 'db_connection.php';

$query = $_GET['q'];
$sql = "SELECT DrugID, DrugName, StockQuantity, Price FROM drugs WHERE DrugName LIKE '%$query%'";
$result = $conn->query($sql);

$drugs = [];
while ($row = $result->fetch_assoc()) {
    $drugs[] = $row;
}

echo json_encode($drugs);

$conn->close();
?>
