<?php
include('db.php');
session_start();

if (!isset($_GET['id'])) die("Akses dinafikan.");

$id = intval($_GET['id']);
$query = $conn->prepare("SELECT fail_surat FROM minit_surat WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$row = $result->fetch_assoc();

// Debugging: Semak apa yang ada dalam database
if (!$row) {
    die("ID $id tidak wujud dalam database.");
}

if (empty($row['fail_surat'])) {
    die("Database menunjukkan column 'fail_surat' untuk ID $id adalah KOSONG.");
}

$file_path = __DIR__ . '/uploads/' . $row['fail_surat'];

if (file_exists($file_path)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
    readfile($file_path);
    exit;
} else {
    // Ini akan beritahu anda lokasi sebenar fail yang dicari
    die("Fail tidak dijumpai di lokasi: " . $file_path);
}
?>
