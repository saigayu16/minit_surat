<?php
$host = getenv('NEON_DB_HOST');
$db   = getenv('NEON_DB_NAME');
$user = getenv('NEON_DB_USER'); // Ini akan mengambil 'neondb_owner' dari Render
$pass = getenv('NEON_DB_PASSWORD');
$port = getenv('NEON_DB_PORT');

$dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";

try {
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection to Neon failed: " . $e->getMessage());
}
?>
