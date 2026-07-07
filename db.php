<?php
// DO NOT CHANGE THESE KEYS. They point to your Render Dashboard settings.
$host = getenv('mysql-aae9a8-minitsurat123.c.aivencloud.com');
$user = getenv('avnadmin');
$pass = getenv('DB_PASSWORD');
$db   = getenv('defaultdb');
$port = getenv('22408');

// If this prints, it means you haven't set the variables in the Render Dashboard yet
if (!$host || !$user || !$pass || !$db || !$port) {
    die("Error: Please set all 5 Environment Variables (DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT) in your Render Dashboard.");
}

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
