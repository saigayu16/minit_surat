<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('db.php'); // Pastikan $conn anda adalah objek PDO

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil input (Tiada mysqli_real_escape_string dalam PDO)
    $no_rujukan    = $_POST['no_rujukan'];
    $tarikh_terima = $_POST['tarikh_terima'];
    $daripada      = $_POST['daripada'];
    $perkara       = $_POST['perkara'];
    $kolej         = $_POST['kolej'];
    $target_role   = $_POST['target_role'];
    
    // 2. Dapatkan Emel Penerima (Guna PDO)
    $stmt_email = $conn->prepare("SELECT email FROM users WHERE role = ? LIMIT 1");
    $stmt_email->execute([$target_role]);
    $user = $stmt_email->fetch(PDO::FETCH_ASSOC);
    $email_penerima = $user ? $user['email'] : null;
    
    if (!$email_penerima) die("Ralat: Tiada emel untuk role $target_role");

    // 3. Proses Fail ke Google Drive
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
        curl_setopt($ch_drive, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch_drive, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $drive_response = trim(curl_exec($ch_drive));
        curl_close($ch_drive);

        if (!empty($drive_response)) {
            $drive_file_id = $drive_response;
        }
    }

    // 4. Integrasi API Brevo
    $api_key = getenv('BREVO_API_KEY');
    $data = [
        "sender" => ["email" => "saigayu1605@gmail.com", "name" => "Sistem Minit Digital"],
        "to" => [["email" => $email_penerima]],
        "subject" => "Notifikasi: Surat Baharu - " . $no_rujukan,
        "htmlContent" => "Assalamualaikum, terdapat surat baharu untuk tindakan anda.",
        "attachment" => [["content" => $base64_file, "name" => $file_name]]
    ];

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['api-key: ' . $api_key, 'Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);

    // 5. Simpan ke Database (Guna PDO)
    $sql = "INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, target_role, status, drive_file_id) 
            VALUES (?, ?, ?, ?, ?, ?, 'BARU', ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$no_rujukan, $tarikh_terima, $daripada, $perkara, $kolej, $target_role, $drive_file_id])) {
        echo "<script>alert('Surat telah didaftarkan! (Drive ID: $drive_file_id)'); window.location='homeadmin.php';</script>";
    } else {
        $errorInfo = $stmt->errorInfo();
        echo "Ralat Database: " . $errorInfo[2];
    }
}
?>
