<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_rujukan = mysqli_real_escape_string($conn, $_POST['no_rujukan']);
    $tarikh_terima = mysqli_real_escape_string($conn, $_POST['tarikh_terima']);
    $daripada = mysqli_real_escape_string($conn, $_POST['daripada']);
    $perkara = mysqli_real_escape_string($conn, $_POST['perkara']);
    $kolej = mysqli_real_escape_string($conn, $_POST['kolej']);
    $target_role = mysqli_real_escape_string($conn, $_POST['target_role']);
    
    $stmt_email = $conn->prepare("SELECT email FROM users WHERE role = ? LIMIT 1");
    $stmt_email->bind_param("s", $target_role);
    $stmt_email->execute();
    $result = $stmt_email->get_result();
    $email_penerima = ($result->num_rows > 0) ? $result->fetch_assoc()['email'] : null;
    
    if (!$email_penerima) die("Ralat: Tiada emel untuk role $target_role");

    // Proses Fail ke Google Drive
    if (isset($_FILES['fail_surat']) && $_FILES['fail_surat']['error'] == 0) {
        $file_name = $_FILES['fail_surat']['name'];
        $base64_file = base64_encode(file_get_contents($_FILES['fail_surat']['tmp_name']));
        $payload = json_encode(['fileData' => $base64_file, 'mimeType' => 'application/pdf', 'fileName' => $file_name]);
        
        $ch_drive = curl_init("https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec");
        curl_setopt($ch_drive, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch_drive, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_drive, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $drive_response = trim(curl_exec($ch_drive));
        $http_code_drive = curl_getinfo($ch_drive, CURLINFO_HTTP_CODE);
        curl_close($ch_drive);

        // Pastikan ID tidak terlalu panjang untuk database
        $drive_file_id = ($http_code_drive == 200 && strlen($drive_response) < 200) ? $drive_response : "GAGAL_UPLOAD";
    } else {
        die("Fail tidak dijumpai.");
    }

    // Integrasi Brevo
    $api_key = getenv('BREVO_API_KEY');
    $data = ["sender" => ["email" => "saigayu1605@gmail.com", "name" => "Sistem Minit Digital"], "to" => [["email" => $email_penerima]], "subject" => "Notifikasi: Surat Baharu - " . $no_rujukan, "htmlContent" => "Assalamualaikum, terdapat surat baharu untuk tindakan anda.", "attachment" => [["content" => $base64_file, "name" => $file_name]]];

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['api-key: ' . $api_key, 'Content-Type: application/json']);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Simpan ke Database (Langkah Akhir)
    $stmt = $conn->prepare("INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, target_role, status, drive_file_id) VALUES (?, ?, ?, ?, ?, ?, 'BARU', ?)");
    $stmt->bind_param("sssssss", $no_rujukan, $tarikh_terima, $daripada, $perkara, $kolej, $target_role, $drive_file_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Berjaya dihantar!'); window.location='homeadmin.php';</script>";
    } else {
        echo "Ralat Database: " . $stmt->error;
    }
}
?>
