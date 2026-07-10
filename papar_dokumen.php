<?php
// papar_dokumen.php
include('db.php');
session_start();

if (!isset($_GET['id'])) {
    die("Akses dinafikan.");
}

$id = intval($_GET['id']);
$query = $conn->prepare("SELECT fail_surat FROM minit_surat WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$row = $result->fetch_assoc();

if ($row && !empty($row['fail_surat'])) {
    // Sila pastikan laluan 'uploads/' adalah tepat mengikut struktur folder anda
    $file_path = __DIR__ . '/uploads/' . $row['fail_surat'];
    
    if (file_exists($file_path)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
        readfile($file_path);
        exit;
    } else {
        die("Fail tidak dijumpai di laluan: " . $file_path);
    }
} else {
    die("Tiada fail dikaitkan dengan dokumen ini.");
}
?>
