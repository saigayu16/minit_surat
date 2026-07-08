<?php
// 1. Ambil nilai dan pastikan tiada ruang kosong yang tidak sengaja (trim)
$host = trim(getenv('DB_HOST'));
$user = trim(getenv('DB_USER'));
$pass = trim(getenv('DB_PASSWORD'));
$db   = trim(getenv('DB_NAME'));
$port = (int)getenv('DB_PORT'); // Tukar kepada integer untuk port

// 2. Semakan untuk memastikan variabel wujud
if (empty($host) || empty($user) || empty($db) || empty($port)) {
    die("Error: Sila pastikan DB_HOST, DB_USER, DB_NAME, dan DB_PORT telah ditetapkan dalam Render Dashboard.");
}

// 3. Sambungan MySQLi
$conn = new mysqli($host, $user, $pass, $db, $port);

// 4. Periksa sambungan
if ($conn->connect_error) {
    // Sembunyikan $pass dalam mesej ralat untuk keselamatan
    die("Connection failed: " . $conn->connect_error);
}

// Set charset kepada utf8mb4 supaya tiada ralat aksara
$conn->set_charset("utf8mb4");
?>
