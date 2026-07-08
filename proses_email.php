<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['surat_id'];
    $email = $_POST['email'];
    $nama_staf = $_POST['nama_staf'];

    // 1. Semakan Staf
    $stmt_check = $conn->prepare("SELECT nama FROM staff WHERE email = ? AND nama = ?");
    $stmt_check->bind_param("ss", $email, $nama_staf);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        echo "<script>alert('Ralat: Maklumat staf tidak sah!'); window.history.back();</script>";
        exit;
    }

    // 2. Proses Fail (SIMPAN KE DATABASE BUKAN FOLDER UNTUK RENDER)
    if (isset($_FILES['dokumen_minit']) && $_FILES['dokumen_minit']['error'] == 0) {
        $file_data = file_get_contents($_FILES['dokumen_minit']['tmp_name']);
        $file_name = $_FILES['dokumen_minit']['name'];
    } else {
        echo "<script>alert('Fail diperlukan.'); window.history.back();</script>";
        exit;
    }

    // 3. Setup & Hantar E-mel (GUNAKAN BREVO SMTP RELAY)
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io'; // From your screenshot
        $mail->SMTPAuth   = true;
        $mail->Username   = '8bcee3755ce00c';           // From your screenshot
        $mail->Password   = 'f3ad70a431130e';// Click the eye icon next to the asterisks to see the real password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587; // You can use 2525 or 587
        $mail->setFrom('no-reply@sistemanda.com', 'Sistem Minit Digital');
        $mail->addAddress($email);
        
        // Add attachment from string (binary data from database)
        $mail->addStringAttachment($file_data, $file_name);

        $mail->isHTML(true);
        $mail->Subject = 'Notifikasi Minit Surat';
        $mail->Body    = "Hai <strong>$nama_staf</strong>,<br><br>Anda telah dimaklumkan mengenai surat ini. Sila rujuk dokumen minit yang dilampirkan.<br><br>Terima kasih.";

        $mail->send();

        // 4. Kemaskini Database
        $stmt = $conn->prepare("UPDATE minit_surat SET status = 'DIMAKLUM', maklum_kepada = ? WHERE id = ?");
        $stmt->bind_param("ss", $nama_staf, $id);
        $stmt->execute();

        echo "<script>alert('Berjaya!'); window.location='homeadmin.php';</script>";
              
    } catch (Exception $e) {
        echo "<script>alert('E-mel gagal: " . addslashes($mail->ErrorInfo) . "'); window.history.back();</script>";
    }
}
?>
