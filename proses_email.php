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

    // 2. Proses Fail
    if (isset($_FILES['dokumen_minit']) && $_FILES['dokumen_minit']['error'] == 0) {
        $file_data = file_get_contents($_FILES['dokumen_minit']['tmp_name']);
        $file_name = $_FILES['dokumen_minit']['name'];
    } else {
        echo "<script>alert('Fail diperlukan.'); window.history.back();</script>";
        exit;
    }

    // 3. Setup & Hantar E-mel
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = getenv('sandbox.smtp.mailtrap.io');
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('8bcee3755ce00c');
        $mail->Password   = getenv('f3ad70a431130e');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)getenv('MAIL_PORT');
        $mail->Timeout    = 15; // Had masa 15 saat

        $mail->setFrom('no-reply@minitsurat.com', 'Sistem Minit Digital');
        $mail->addAddress($email);
        $mail->addStringAttachment($file_data, $file_name);
        $mail->isHTML(true);
        $mail->Subject = 'Notifikasi Minit Surat';
        $mail->Body    = "Hai <strong>$nama_staf</strong>,<br><br>Anda telah dimaklumkan mengenai surat ini. Sila rujuk dokumen minit yang dilampirkan.";

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
