<?php
// Pastikan Railway/Hosting anda telah set variables ini
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');

// Sambungan menggunakan mysqli
$conn = new mysqli($host, $user, $pass, $db, $port);

// Semak sambungan
if ($conn->connect_error) {
    die("Sambungan Gagal: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
?>
