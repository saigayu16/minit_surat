<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Pastikan path ke fail PHPMailer betul
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil data dari form
    $id = $_POST['surat_id'];
    $email = $_POST['email'];
    $nama_staf = $_POST['nama_staf'];

    // 2. Semakan staf dalam database
    $stmt_check = $conn->prepare("SELECT nama FROM staff WHERE email = ? AND nama = ?");
    $stmt_check->bind_param("ss", $email, $nama_staf);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('Ralat: Maklumat staf tidak sah!'); window.history.back();</script>";
        exit;
    }

    // 3. Validasi Fail (Tanpa perlu simpan ke folder server)
    if (!isset($_FILES['dokumen_minit']) || $_FILES['dokumen_minit']['error'] !== UPLOAD_ERR_OK) {
        echo "<script>alert('Sila pilih fail untuk dimuat naik.'); window.history.back();</script>";
        exit;
    }

    // 4. Setup & Hantar E-mel (PHPMailer)
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'saigayu1605@gmail.com'; 
        $mail->Password = 'sspxgfwadkfghbfs'; // Pastikan ini App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('saigayu1605@gmail.com', 'Sistem Minit Digital');
        $mail->addAddress($email);

        // LAMPIRKAN FAIL TERUS DARI BUFFER SEMENTARA
        $mail->addAttachment($_FILES['dokumen_minit']['tmp_name'], $_FILES['dokumen_minit']['name']);

        $mail->isHTML(true);
        $mail->Subject = 'Notifikasi Minit Surat';
        $mail->Body    = "Hai <strong>$nama_staf</strong>,<br><br>Anda telah dimaklumkan mengenai surat ini. Sila rujuk dokumen minit yang dilampirkan.<br><br>Terima kasih.";

        $mail->send();

        // 5. Kemaskini Database
        $stmt = $conn->prepare("UPDATE minit_surat SET status = 'DIMAKLUM', maklum_kepada = ? WHERE id = ?");
        $stmt->bind_param("ss", $nama_staf, $id);
        $stmt->execute();

        echo "<script>alert('E-mel berjaya dihantar!'); window.location='homeadmin.php';</script>";
              
    } catch (Exception $e) {
        echo "<script>alert('E-mel gagal dihantar. Ralat: " . addslashes($mail->ErrorInfo) . "'); window.history.back();</script>";
    }
}
?>
