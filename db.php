<?php
// These are the names of the variables set in your Render Dashboard
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');

// If one of these is missing, it will tell you in the browser
if (!$host || !$user || !$pass || !$db || !$port) {
    die("Error: Please set all 5 Environment Variables (DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT) in your Render Dashboard.");
}

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
