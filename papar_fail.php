<?php
// papar_fail.php
include('db.php');
if (!isset($_GET['id'])) { die("ID tiada"); }

$id = intval($_GET['id']);
// Pastikan nama column 'fail_surat' adalah betul (jenis LONGBLOB)
$res = $conn->query("SELECT fail_surat FROM minit_surat WHERE id = $id");

if ($row = $res->fetch_assoc()) {
    header("Content-Type: application/pdf");
    echo $row['fail_surat']; 
} else {
    echo "Fail tidak dijumpai.";
}
?>
