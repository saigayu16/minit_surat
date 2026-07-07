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
    
    // 1. Ambil data
    $no_rujukan     = mysqli_real_escape_string($conn, $_POST['no_rujukan'] ?? '');
    $tarikh_terima  = mysqli_real_escape_string($conn, $_POST['tarikh_terima'] ?? '');
    $daripada       = mysqli_real_escape_string($conn, $_POST['daripada'] ?? '');
    $perkara        = mysqli_real_escape_string($conn, $_POST['perkara'] ?? '');
    $kolej          = mysqli_real_escape_string($conn, $_POST['kolej'] ?? '');
    $target_role    = mysqli_real_escape_string($conn, $_POST['target_role'] ?? 'pengarah'); // Role yang dipilih Admin
    
    $status         = "BARU";
    $didaftarkan_oleh = $_SESSION['user_name'] ?? 'Admin';

    // 2. Ambil emel penerima berdasarkan Role yang dipilih
    $stmt_email = $conn->prepare("SELECT email FROM users WHERE role = ? LIMIT 1");
    $stmt_email->bind_param("s", $target_role);
    $stmt_email->execute();
    $result_email = $stmt_email->get_result();
    $user_penerima = $result_email->fetch_assoc();
    $penerima_email = $user_penerima['email'] ?? 'admin@kkkb.edu.my'; // Fallback jika emel tiada

    // 3. Proses Fail
    $nama_fail_baru = time() . '_' . uniqid() . '.pdf';
    $folder_tujuan  = "uploads/" . $nama_fail_baru;

    if (move_uploaded_file($_FILES['fail_surat']['tmp_name'], $folder_tujuan)) {
        
        // 4. SQL Insert (Tambah column target_role)
        // Pastikan column 'target_role' ada dalam table minit_surat
        $sql = "INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, didaftarkan_oleh, fail_surat, status, target_role) 
                VALUES ('$no_rujukan', '$tarikh_terima', '$daripada', '$perkara', '$kolej', '$didaftarkan_oleh', '$nama_fail_baru', '$status', '$target_role')";
        
        if ($conn->query($sql) === TRUE) {
            $id_surat_baru = $conn->insert_id;

            // 5. Konfigurasi PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'saigayu1605@gmail.com'; 
                $mail->Password   = 'sspxgfwadkfghbfs';      
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('saigayu1605@gmail.com', 'Sistem Minit Surat Digital');
                $mail->addAddress($penerima_email); 

                $url_sistem = "http://localhost/minit_surat/tandatangan.php?id=" . $id_surat_baru;
                
                $mail->isHTML(true);
                $mail->Subject = "🔴 NOTIFIKASI: Minit Surat Baharu - " . $no_rujukan;
                $mail->Body    = "
                    <div style='font-family: Arial;'>
                        <h2>Surat Masuk Baharu</h2>
                        <p>Tuan/Puan, terdapat surat baharu untuk tindakan tuan:</p>
                        <p><b>Rujukan:</b> {$no_rujukan}<br><b>Perkara:</b> {$perkara}<br><b>Kolej:</b> {$kolej}</p>
                        <a href='{$url_sistem}' style='background:blue; color:white; padding:10px; text-decoration:none;'>Klik Sini Untuk Semakan/Tandatangan</a>
                    </div>";

                $mail->send();
                echo "<script>alert('Surat berjaya didaftar & dihantar kepada $target_role!'); window.location.href='homeadmin.php';</script>";

            } catch (Exception $e) {
                echo "<script>alert('Surat didaftar, tetapi e-mel gagal: {$mail->ErrorInfo}'); window.location.href='homeadmin.php';</script>";
            }
        }
    } else {
        echo "<script>alert('Gagal memuat naik fail.'); window.history.back();</script>";
    }
}
?>
