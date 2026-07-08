<?php
// Ambil data dari Environment Variable
$host = trim(getenv('DB_HOST'));
$user = trim(getenv('DB_USER'));
$pass = trim(getenv('DB_PASSWORD'));
$db   = trim(getenv('DB_NAME'));
$port = (int)getenv('DB_PORT');

// SEMAKAN DIAGNOSTIK
if (empty($host)) {
    die("CRITICAL ERROR: DB_HOST tidak dijumpai. Sila semak 'Environment' di Render Dashboard.");
}

// Cuba sambung
mysqli_report(MYSQLI_REPORT_OFF); // Matikan error biasa untuk kita tangkap sendiri
$conn = @new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . " <br><br> 
    Punca mungkin: 
    1. Alamat HOST salah. 
    2. IP Render tidak dibenarkan oleh Aiven. 
    3. Port salah.");
}

$conn->set_charset("utf8mb4");
?>
