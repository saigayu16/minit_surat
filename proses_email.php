<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Pastikan path ke fail PHPMailer adalah betul mengikut folder projek anda
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil data dari form
    $id = $_POST['surat_id'];
    $email = $_POST['email'];
    $nama_staf = $_POST['nama_staf'];

    // 2. Semakan: Adakah staf wujud dalam database?
    $stmt_check = $conn->prepare("SELECT nama FROM staff WHERE email = ? AND nama = ?");
    $stmt_check->bind_param("ss", $email, $nama_staf);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows === 0) {
        echo "<script>
                alert('Ralat: Maklumat staf tidak sah! Sila pastikan nama dan e-mel sepadan dengan rekod.'); 
                window.history.back();
              </script>";
        exit;
    }

    // 3. Proses Upload Fail
    if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }
    
    $file_name = time() . "_" . basename($_FILES["dokumen_minit"]["name"]);
    $target_file = "uploads/" . $file_name;
    
    if (!move_uploaded_file($_FILES["dokumen_minit"]["tmp_name"], $target_file)) {
        echo "<script>alert('Gagal memuat naik fail.'); window.history.back();</script>";
        exit;
    }

    // 4. Setup & Hantar E-mel (SMTP)
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'saigayu1605@gmail.com'; 
        $mail->Password = 'sspxgfwadkfghbfs'; // App Password anda
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('saigayu1605@gmail.com', 'Sistem Minit Digital');
        $mail->addAddress($email);
        $mail->addAttachment($target_file);

        $mail->isHTML(true);
        $mail->Subject = 'Notifikasi Minit Surat';
        $mail->Body    = "Hai <strong>$nama_staf</strong>,<br><br>Anda telah dimaklumkan mengenai surat ini. Sila rujuk dokumen minit yang dilampirkan.<br><br>Terima kasih.";

        $mail->send();

        // 5. Kemaskini Database
        // Mengemaskini status dan menyimpan nama staf dalam kolum maklum_kepada
        $stmt = $conn->prepare("UPDATE minit_surat SET status = 'DIMAKLUM', maklum_kepada = ? WHERE id = ?");
        $stmt->bind_param("ss", $nama_staf, $id);
        $stmt->execute();

        // 6. Redirect ke halaman admin
        echo "<script>
                alert('E-mel berjaya dihantar dan status surat dikemaskini kepada DIMAKLUM!'); 
                window.location='homeadmin.php';
              </script>";
              
    } catch (Exception $e) {
        // Paparkan ralat jika e-mel gagal dihantar
        echo "<script>
                alert('E-mel gagal dihantar. Ralat: " . addslashes($mail->ErrorInfo) . "'); 
                window.history.back();
              </script>";
    }
}
?>
