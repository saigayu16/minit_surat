<?php
$host = "mysql-aae9a8-minitsurat123.c.aivencloud.com"; 
$user = "avnadmin";
// "DB_PASSWORD" is the NAME of the variable, not the password itself
$pass = getenv("DB_PASSWORD"); 
$dbname = "defaultdb";
$port = 22408;

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Sambungan database gagal: " . $conn->connect_error);
}
?>
