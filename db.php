<?php
// 1. Cuba ambil maklumat Neon dahulu
$neon_host = getenv('NEON_DB_HOST');

if ($neon_host) {
    // --- MODE NEON (PostgreSQL) ---
    $host = trim($neon_host);
    $user = trim(getenv('NEON_DB_USER'));
    $pass = trim(getenv('NEON_DB_PASSWORD'));
    $db   = trim(getenv('NEON_DB_NAME'));
    $port = (int)getenv('NEON_DB_PORT');

    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";
        $conn = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        // Berjaya sambung ke Neon
    } catch (PDOException $e) {
        die("Connection to Neon failed: " . $e->getMessage());
    }

} else {
    // --- MODE AIVEN (MySQL) - Kod Asal Anda ---
    $host = trim(getenv('DB_HOST'));
    $user = trim(getenv('DB_USER'));
    $pass = trim(getenv('DB_PASSWORD'));
    $db   = trim(getenv('DB_NAME'));
    $port = (int)getenv('DB_PORT');

    $conn = mysqli_init();
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
    if (!$conn->real_connect($host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT)) {
        die("Connection to Aiven failed: " . mysqli_connect_error());
    }
    $conn->set_charset("utf8mb4");
}
?>
