<?php
// Pastikan anda telah include fail sambungan database anda
include('db.php');

// 1. Dapatkan ID daripada URL dan pastikan ia adalah integer
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // 2. Ambil data daripada database
    // Pastikan nama kolum 'drive_file_id' dan 'fail_surat' adalah tepat mengikut database anda
    $query = "SELECT drive_file_id, fail_surat FROM minit_surat WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        // Keutamaan 1: Fail Tempatan (Folder 'uploads/')
        // Kita guna path relatif yang betul supaya iframe boleh capai
        $path_tempatan = "uploads/" . $row['fail_surat'];
        
        if (!empty($row['fail_surat']) && file_exists($path_tempatan)) {
            // Berikan laluan fail supaya iframe boleh buka
            echo $path_tempatan;
        } 
        // Keutamaan 2: Google Drive
        // Pastikan link Google Drive menggunakan /preview untuk paparan dalam iframe
        else if (!empty($row['drive_file_id']) && $row['drive_file_id'] !== "GAGAL_UPLOAD") {
            echo "https://drive.google.com/file/d/" . trim($row['drive_file_id']) . "/preview";
        } 
        else {
            // Jika kedua-dua tiada
            echo "ERROR_FILE_NOT_FOUND";
        }
    } else {
        echo "ERROR_RECORD_NOT_FOUND";
    }
} else {
    echo "ERROR_INVALID_ID";
}
?>
