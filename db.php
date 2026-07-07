<?php
// IMPORTANT: NO spaces or lines before the <?php tag!
$host = getenv('mysql-aae9a8-minitsurat123.c.aivencloud.com');
$user = getenv('avnadmin');
$pass = getenv('22408');
$db   = getenv('defaultdb');

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Do not have any echo or print statements here!
?>

