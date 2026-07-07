<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(120); // Give the script 2 minutes to process large files
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

    if (isset($_FILES['fail_surat']) && $_FILES['fail_surat']['error'] == 0) {
        $file_data = file_get_contents($_FILES['fail_surat']['tmp_name']);

        // Insert into database
        $sql = "INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, didaftarkan_oleh, fail_surat, status, target_role) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        // Use 'b' for binary data (LONGBLOB)
        $null = NULL; 
        $stmt->bind_param("ssssssbss", $no_rujukan, $tarikh_terima, $daripada, $perkara, $kolej, $didaftarkan_oleh, $null, $status, $target_role);
        $stmt->send_long_data(6, $file_data); // Send blob data in chunks

        if ($stmt->execute()) {
            $id_surat_baru = $stmt->insert_id;

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->SMTPDebug = 2; // CHANGE THIS TO 0 ONCE IT WORKS
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'saigayu1605@gmail.com'; 
                $mail->Password   = 'aewm gplr ochy hesq'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('saigayu1605@gmail.com', 'Sistem Minit Surat Digital');
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
                echo "<script>alert('Error Emel: {$mail->ErrorInfo}'); window.location.href='homeadmin.php';</script>";
            }
        } else {
            echo "Error Database: " . $stmt->error;
        }
    } else {
        echo "<script>alert('Sila muat naik fail PDF!'); window.history.back();</script>";
    }
}
?>
