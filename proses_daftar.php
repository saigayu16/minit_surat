<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('db.php');
require_once('mailer.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_rujukan    = $_POST['no_rujukan'];
    $tarikh_terima = $_POST['tarikh_terima'];
    $daripada      = $_POST['daripada'];
    $perkara       = $_POST['perkara'];
    $kolej         = $_POST['kolej'];
    $target_role   = $_POST['target_role'];
    
    // 1. Dapatkan Emel Penerima
    $stmt_email = $conn->prepare("SELECT email FROM users WHERE role = ? LIMIT 1");
    $stmt_email->execute([$target_role]);
    $user = $stmt_email->fetch(PDO::FETCH_ASSOC);
    $email_penerima = $user['email'] ?? null;
    
    if (!$email_penerima) die("Ralat: Tiada emel untuk role $target_role");

    // 2. Proses Fail ke Google Drive
    $drive_file_id = "GAGAL_UPLOAD";
    $base64_file = "";
    $file_name = "";

    if (isset($_FILES['fail_surat']) && $_FILES['fail_surat']['error'] == 0) {
        $file_name = $_FILES['fail_surat']['name'];
        $base64_file = base64_encode(file_get_contents($_FILES['fail_surat']['tmp_name']));
        
        $payload = json_encode(['fileData' => $base64_file, 'mimeType' => 'application/pdf', 'fileName' => $file_name]);
        $ch_drive = curl_init("https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec");
        curl_setopt($ch_drive, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch_drive, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_drive, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $drive_file_id = trim(curl_exec($ch_drive));
        curl_close($ch_drive);
    }

    // 3. Hantar Emel
    $subjek = "Notifikasi: Surat Baharu - " . $no_rujukan;
    $mesej = "Assalamualaikum, terdapat surat baharu untuk tindakan anda.";
    $status_emel = hantarEmail($email_penerima, "Penerima", $subjek, $mesej, $base64_file, $file_name);

    // 4. Simpan ke Database
    $sql = "INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, target_role, status, drive_file_id) VALUES (?, ?, ?, ?, ?, ?, 'BARU', ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$no_rujukan, $tarikh_terima, $daripada, $perkara, $kolej, $target_role, $drive_file_id])) {
        $msg = $status_emel ? "Surat berjaya didaftarkan dan emel telah dihantar!" : "Surat berjaya didaftarkan, TAPI emel gagal dihantar!";
        echo "<script>alert('$msg'); window.location='homeadmin.php';</script>";
    } else {
        echo "Ralat Database.";
    }
}
?>
