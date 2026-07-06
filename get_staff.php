<?php
include('db.php');
$term = $_GET['term'];
$query = $conn->query("SELECT nama as label, email FROM staff WHERE nama LIKE '%$term%'");
$data = [];
while($row = $query->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
?>