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
        die("<script>alert('Ralat: Maklumat staf tidak sah!'); window.history.back();</script>");
    }

    // 2. Proses Upload Fail
    if (!isset($_FILES['dokumen_minit']) || $_FILES['dokumen_minit']['error'] !== UPLOAD_ERR_OK) {
        $error_code = $_FILES['dokumen_minit']['error'] ?? 'Tiada fail';
        die("<script>alert('Gagal muat naik. Kod Ralat: $error_code'); window.history.back();</script>");
    }

    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
    
    $file_name = time() . "_" . basename($_FILES["dokumen_minit"]["name"]);
    $target_file = $upload_dir . $file_name;
    
    if (!move_uploaded_file($_FILES["dokumen_minit"]["tmp_name"], $target_file)) {
        die("<script>alert('Gagal memindahkan fail ke folder.'); window.history.back();</script>");
    }

    // 3. Setup & Hantar E-mel (PHPMailer)
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
        $mail->addAttachment($target_file);

        $mail->isHTML(true);
        $mail->Subject = 'Notifikasi Minit Surat';
        $mail->Body = "Hai <strong>$nama_staf</strong>,<br><br>Anda telah dimaklumkan mengenai surat ini. Sila rujuk dokumen minit yang dilampirkan.<br><br>Terima kasih.";

        $mail->send();

        // 4. Update Database
        $stmt = $conn->prepare("UPDATE minit_surat SET status = 'DIMAKLUM', maklum_kepada = ? WHERE id = ?");
        $stmt->bind_param("ss", $nama_staf, $id);
        $stmt->execute();

        echo "<script>alert('Berjaya!'); window.location='homeadmin.php';</script>";
              
    } catch (Exception $e) {
        echo "<script>alert('E-mel gagal dihantar: " . addslashes($mail->ErrorInfo) . "'); window.history.back();</script>";
    }
}
?>
