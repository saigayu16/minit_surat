<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "db_minit_surat"; // <-- Tukar kepada nama database anda jika berbeza

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Sambungan database gagal: " . $conn->connect_error);
}
?>

