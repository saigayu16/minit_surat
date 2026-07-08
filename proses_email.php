<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['surat_id'];
    $email_input = $_POST['email'];
    $nama_staf = $_POST['nama_staf'];

    // 1. Semakan Staf (Mesti wujud dalam database)
    $stmt_check = $conn->prepare("SELECT email FROM staff WHERE email = ? AND nama = ?");
    $stmt_check->bind_param("ss", $email_input, $nama_staf);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        echo "<script>alert('Ralat: Maklumat staf tidak sah!'); window.history.back();</script>";
        exit;
    }

    // 2. Setup PHPMailer menggunakan Environment Variables
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        // Render akan baca nilai dari "Environment" di dashboard
        $mail->Host       = getenv('BREVO_HOST'); 
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('BREVO_USER');
        $mail->Password   = getenv('BREVO_PASS'); 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)getenv('BREVO_PORT');
        $mail->Timeout    = 15;

        $mail->setFrom('sistem@minitdigital.com', 'Sistem Minit Digital');
        $mail->addAddress($email_input);
        $mail->isHTML(true);
        $mail->Subject = 'Notifikasi Minit Surat';
        $mail->Body    = "Hai <strong>$nama_staf</strong>,<br><br>Anda telah dimaklumkan mengenai surat ini.";

        $mail->send();

        // 3. Kemaskini Database
        $stmt = $conn->prepare("UPDATE minit_surat SET status = 'DIMAKLUM', maklum_kepada = ? WHERE id = ?");
        $stmt->bind_param("si", $nama_staf, $id);
        $stmt->execute();

        echo "<script>alert('Berjaya!'); window.location='homeadmin.php';</script>";
            
    } catch (Exception $e) {
        echo "<script>alert('E-mel gagal: " . addslashes($mail->ErrorInfo) . "'); window.history.back();</script>";
    }
}
?>
