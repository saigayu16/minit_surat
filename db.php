<?php
$host = getenv('mysql-aae9a8-minitsurat123.c.aivencloud.com');
$user = getenv('avnadmin');
$pass = getenv('DB_PASSWORD'); // This calls the value from the Render Dashboard
$db   = getenv('defaultdb');
$port = getenv('22408');
// Ensure $host is not null/empty
if (!$host) {
    die("Error: DB_HOST is not set!");
}

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
