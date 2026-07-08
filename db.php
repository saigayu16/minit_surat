<?php
$host = trim(getenv('DB_HOST'));
$user = trim(getenv('DB_USER'));
$pass = trim(getenv('DB_PASSWORD'));
$db   = trim(getenv('DB_NAME'));
$port = (int)getenv('DB_PORT');

// Inisialisasi mysqli
$conn = mysqli_init();

// PENTING: Tambah SSL untuk Aiven
// Aiven perlukan SSL, kita set MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT 
// untuk tujuan ujian supaya sambungan tidak gagal kerana sijil
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

if (!$conn->real_connect($host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT)) {
    die("Connection failed: " . mysqli_connect_error());
}

$conn->set_charset("utf8mb4");
?>
