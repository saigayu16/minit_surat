<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('db.php');

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
        // TUKAR: Simpan data fail sebagai blob terus menggunakan prepared statement
        $file_data = file_get_contents($_FILES['fail_surat']['tmp_name']);
        
        $sql = "INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, didaftarkan_oleh, fail_surat, status, target_role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $no_rujukan, $tarikh_terima, $daripada, $perkara, $kolej, $didaftarkan_oleh, $file_data, $status, $target_role);

        if ($stmt->execute()) {
            // DAPATKAN EMEL PENERIMA
            $stmt_email = $conn->prepare("SELECT email FROM users WHERE role = ? LIMIT 1");
            $stmt_email->bind_param("s", $target_role);
            $stmt_email->execute();
            $result_email = $stmt_email->get_result()->fetch_assoc();
            $email_penerima = $result_email['email'] ?? 'admin@kkkb.edu.my';

            // HANTAR EMEL GUNA BREVO API (TIADA LAGI LOADING)
            $api_key = getenv('BREVO_API_KEY');
            $data = [
                "sender" => ["email" => "no-reply@minitsurat.com", "name" => "Sistem Minit Digital"],
                "to" => [["email" => $email_penerima]],
                "subject" => "NOTIFIKASI: Minit Surat Baharu - " . $no_rujukan,
                "htmlContent" => "Terdapat surat baharu untuk tindakan tuan/puan. Sila semak sistem."
            ];

            $ch = curl_init('https://api.brevo.com/v3/smtp/email');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['api-key: ' . $api_key, 'Content-Type: application/json']);
            curl_exec($ch);
            curl_close($ch);

            echo "<script>alert('Berjaya!'); window.location.href='homeadmin.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "<script>alert('Sila pilih fail PDF.'); window.history.back();</script>";
    }
}
?>
