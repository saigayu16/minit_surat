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
    $no_rujukan = $_POST['no_rujukan'] ?? '';
    $tarikh_terima = $_POST['tarikh_terima'] ?? '';
    $daripada = $_POST['daripada'] ?? '';
    $perkara = $_POST['perkara'] ?? '';
    $kolej = $_POST['kolej'] ?? '';
    $target_role = $_POST['target_role'] ?? 'pengarah';
    $status = "BARU";
    $didaftarkan_oleh = $_SESSION['user_name'] ?? 'Admin';

    // 1. Ambil data fail
    if (isset($_FILES['fail_surat']) && $_FILES['fail_surat']['error'] == 0) {
        $file_data = file_get_contents($_FILES['fail_surat']['tmp_name']);

        // 2. Insert ke Database (Guna ? untuk elak ralat)
        $sql = "INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, didaftarkan_oleh, fail_surat, status, target_role) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        // "sssssssss" maksudnya semua data adalah string/binary
        $stmt->bind_param("sssssssss", $no_rujukan, $tarikh_terima, $daripada, $perkara, $kolej, $didaftarkan_oleh, $file_data, $status, $target_role);

        if ($stmt->execute()) {
            $id_surat_baru = $stmt->insert_id;

            // 3. Email (Pastikan guna App Password Google)
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'saigayu1605@gmail.com'; 
                $mail->Password   = 'aewm gplr ochy hesq'; // Guna 16-char App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('saigayu1605@gmail.com', 'Sistem Minit Surat Digital');
                // Tambah alamat penerima
                $stmt_email = $conn->prepare("SELECT email FROM users WHERE role = ? LIMIT 1");
                $stmt_email->bind_param("s", $target_role);
                $stmt_email->execute();
                $penerima = $stmt_email->get_result()->fetch_assoc();
                $mail->addAddress($penerima['email'] ?? 'admin@kkkb.edu.my'); 

                $mail->isHTML(true);
                $mail->Subject = "NOTIFIKASI: Minit Surat Baharu";
                $mail->Body    = "Terdapat surat baharu. Sila semak sistem.";
                $mail->send();

                echo "<script>alert('Surat berjaya disimpan & emel dihantar!'); window.location.href='homeadmin.php';</script>";
            } catch (Exception $e) {
                echo "<script>alert('Surat disimpan tetapi emel gagal: {$mail->ErrorInfo}'); window.location.href='homeadmin.php';</script>";
            }
        } else {
            echo "Error Database: " . $stmt->error;
        }
    } else {
        echo "<script>alert('Sila muat naik fail PDF!'); window.history.back();</script>";
    }
}
?>
