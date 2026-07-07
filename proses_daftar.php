<?php
// Increase limits for file handling
ini_set('memory_limit', '256M');
ini_set('upload_max_filesize', '20M');
ini_set('post_max_size', '20M');
set_time_limit(300); 

session_start();
include('db.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (isset($_POST['btn_simpan'])) {
    $no_rujukan = $_POST['no_rujukan'] ?? '';
    $daripada = $_POST['daripada'] ?? '';
    $perkara = $_POST['perkara'] ?? '';
    $target_role = $_POST['target_role'] ?? 'pengarah';
    
    if (isset($_FILES['fail_surat']) && $_FILES['fail_surat']['error'] == 0) {
        $file_data = file_get_contents($_FILES['fail_surat']['tmp_name']);

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO minit_surat (no_rujukan, daripada, perkara, fail_surat, target_role, status) VALUES (?, ?, ?, ?, ?, 'BARU')");
        $null = NULL;
        $stmt->bind_param("sssss", $no_rujukan, $daripada, $perkara, $null, $target_role);
        $stmt->send_long_data(3, $file_data);

        if ($stmt->execute()) {
            // Mailtrap Integration
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'sandbox.smtp.mailtrap.io'; // From Mailtrap SMTP settings
                $mail->SMTPAuth   = true;
                $mail->Username   = '8bcee3755ce00c'; // REPLACE WITH YOUR MAILTRAP USERNAME
                $mail->Password   = 'f3ad70a431130e'; // REPLACE WITH YOUR MAILTRAP PASSWORD
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('no-reply@yourdomain.com', 'Sistem Minit Surat');
                $mail->addAddress('admin@kkkb.edu.my'); 
                $mail->Subject = "NOTIFIKASI: Surat Baharu - " . $no_rujukan;
                $mail->Body    = "Surat baharu telah didaftarkan dalam sistem.";

                $mail->send();
                echo "<script>alert('Berjaya!'); window.location.href='homeadmin.php';</script>";
            } catch (Exception $e) {
                echo "Mailer Error: " . $mail->ErrorInfo;
            }
        } else {
            echo "DB Error: " . $stmt->error;
        }
    } else {
        echo "<script>alert('Sila muat naik fail PDF!'); window.history.back();</script>";
    }
}
?>
