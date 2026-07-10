<?php
// papar_dokumen.php
include('db.php');
session_start();

if (!isset($_GET['id'])) die("Akses dinafikan.");

$id = intval($_GET['id']);
$query = $conn->query("SELECT fail_surat FROM minit_surat WHERE id = $id");
$row = $query->fetch_assoc();

if ($row) {
    $file_path = "uploads/" . $row['fail_surat'];
    if (file_exists($file_path)) {
        header('Content-Type: application/pdf');
        readfile($file_path);
    } else {
        echo "Fail tidak dijumpai.";
    }
}
?>
