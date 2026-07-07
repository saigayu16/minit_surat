<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('db.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (isset($_POST['btn_simpan'])) {
    $no_rujukan = mysqli_real_escape_string($conn, $_POST['no_rujukan'] ?? '');
    $tarikh_terima = mysqli_real_escape_string($conn, $_POST['tarikh_terima'] ?? '');
    $daripada = mysqli_real_escape_string($conn, $_POST['daripada'] ?? '');
    $perkara = mysqli_real_escape_string($conn, $_POST['perkara'] ?? '');
    $kolej = mysqli_real_escape_string($conn, $_POST['kolej'] ?? '');
    $target_role = mysqli_real_escape_string($conn, $_POST['target_role'] ?? 'pengarah');
    $status = "BARU";
    $didaftarkan_oleh = $_SESSION['user_name'] ?? 'Admin';

    // Get file content instead of moving it
    if (isset($_FILES['fail_surat']) && $_FILES['fail_surat']['error'] == 0) {
        $file_data = file_get_contents($_FILES['fail_surat']['tmp_name']);
        $file_data = mysqli_real_escape_string($conn, $file_data); // Prepare binary data

        // Insert into database
        $sql = "INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, didaftarkan_oleh, fail_surat, status, target_role) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $no_rujukan, $tarikh_terima, $daripada, $perkara, $kolej, $didaftarkan_oleh, $file_data, $status, $target_role);

        if ($stmt->execute()) {
            $id_surat_baru = $stmt->insert_id;
            
            // ... (Your PHPMailer code remains the same as before) ...
            echo "<script>alert('Surat berjaya didaftar!'); window.location.href='homeadmin.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "<script>alert('Sila pilih fail PDF.'); window.history.back();</script>";
    }
}
?>
