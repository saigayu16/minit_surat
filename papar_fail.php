<?php
include('db.php');

// 1. Dapatkan ID daripada URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // 2. Ambil data daripada database
    $result = $conn->query("SELECT drive_file_id, fail_surat FROM minit_surat WHERE id = $id");
    $row = $result->fetch_assoc();

    if ($row) {
        // Keutamaan 1: Jika fail ada dalam folder 'uploads/' (fail tempatan)
        if (!empty($row['fail_surat']) && file_exists("uploads/" . $row['fail_surat'])) {
            echo "uploads/" . $row['fail_surat'];
        } 
        // Keutamaan 2: Jika tiada fail tempatan, cuba ambil pautan Google Drive
        else if (!empty($row['drive_file_id']) && $row['drive_file_id'] !== "GAGAL_UPLOAD") {
            // Ini adalah pautan untuk paparan Google Drive
            echo "https://drive.google.com/file/d/" . $row['drive_file_id'] . "/view";
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
