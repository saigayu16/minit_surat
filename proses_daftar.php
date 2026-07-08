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

    if (isset($_FILES['fail_surat']) && $_FILES['fail_surat']['error'] == 0) {
        $file_data = file_get_contents($_FILES['fail_surat']['tmp_name']);
        $file_data = mysqli_real_escape_string($conn, $file_data);

        $sql = "INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, didaftarkan_oleh, fail_surat, status, target_role) 
                VALUES ('$no_rujukan', '$tarikh_terima', '$daripada', '$perkara', '$kolej', '$didaftarkan_oleh', '$file_data', '$status', '$target_role')";

        if ($conn->query($sql) === TRUE) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'sandbox.smtp.mailtrap.io';
                $mail->SMTPAuth   = true;
                $mail->Username   = '8bcee3755ce00c';
                $mail->Password   = 'f3ad70a431130e';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // 1. Set sender once
                $mail->setFrom('no-reply@sistemanda.com', 'Sistem Minit Digital');
                
                // 2. Fetch email correctly
                $stmt_email = $conn->prepare("SELECT email FROM users WHERE role = ? LIMIT 1");
                $stmt_email->bind_param("s", $target_role);
                $stmt_email->execute();
                $user_penerima = $stmt_email->get_result()->fetch_assoc();
                $mail->addAddress($user_penerima['email'] ?? 'admin@kkkb.edu.my');

                $mail->isHTML(true);
                $mail->Subject = "NOTIFIKASI: Minit Surat Baharu - " . $no_rujukan;
                $mail->Body    = "Terdapat surat baharu untuk tindakan tuan/puan. Sila semak sistem.";

                $mail->send();
                echo "<script>alert('Berjaya!'); window.location.href='homeadmin.php';</script>";
            } catch (Exception $e) {
                echo "<script>alert('Surat disimpan, tetapi emel gagal: {$mail->ErrorInfo}'); window.location.href='homeadmin.php';</script>";
            }
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "<script>alert('Sila pilih fail PDF.'); window.history.back();</script>";
    }
}
?>
