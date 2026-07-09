<?php
include('db.php');

// 1. Dapatkan ID daripada URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // 2. Ambil data daripada database
    $result = $conn->query("SELECT drive_file_id, fail_surat FROM minit_surat WHERE id = $id");
    $row = $result->fetch_assoc();

    if ($row) {
        // Keutamaan 1: Fail Tempatan
        // Kita berikan laluan penuh/tepat untuk elak ralat
        if (!empty($row['fail_surat']) && file_exists("uploads/" . $row['fail_surat'])) {
            // Untuk paparan fail dalam iframe yang lebih stabil, 
            // jika fail adalah PDF, kita berikan terus laluan fail tersebut.
            echo "uploads/" . $row['fail_surat'];
        } 
        // Keutamaan 2: Google Drive
        // PENTING: Gunakan /preview supaya ia terus dipaparkan (bukan /view)
        else if (!empty($row['drive_file_id']) && $row['drive_file_id'] !== "GAGAL_UPLOAD") {
            echo "https://drive.google.com/file/d/" . $row['drive_file_id'] . "/preview";
        } 
        else {
            echo "ERROR";
        }
    } else {
        echo "ERROR";
    }
} else {
    echo "ERROR";
}
?>
