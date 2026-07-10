<?php
include('db.php');
session_start();

if (!isset($_GET['id'])) die("Akses dinafikan.");

$id = intval($_GET['id']);
$query = $conn->query("SELECT fail_surat FROM minit_surat WHERE id = $id");
$row = $query->fetch_assoc();

if ($row && !empty($row['fail_surat'])) {
    $file_path = "uploads/" . $row['fail_surat'];
    
    if (file_exists($file_path)) {
        // Tetapkan header supaya browser faham ini adalah fail PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));
        
        // Bersihkan output buffer sebelum hantar fail
        ob_clean();
        flush();
        
        readfile($file_path);
        exit;
    } else {
        die("Fail tidak dijumpai di server: " . htmlspecialchars($file_path));
    }
} else {
    die("Data fail tidak dijumpai dalam rekod.");
}
?>
