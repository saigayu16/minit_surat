<?php
include('db.php');
if (!isset($_GET['id'])) { die("ID tidak dijumpai."); }

$id = intval($_GET['id']);
$res = $conn->query("SELECT fail_surat FROM minit_surat WHERE id = $id");
$row = $res->fetch_assoc();

if ($row && !empty($row['fail_surat'])) {
    header("Content-Type: application/pdf");
    echo $row['fail_surat']; 
} else {
    echo "Fail tidak dijumpai atau kosong.";
}
?>
