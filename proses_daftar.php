<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Sanitasi input
    $no_rujukan = mysqli_real_escape_string($conn, $_POST['no_rujukan']);
    $tarikh_terima = mysqli_real_escape_string($conn, $_POST['tarikh_terima']);
    $daripada = mysqli_real_escape_string($conn, $_POST['daripada']);
    $perkara = mysqli_real_escape_string($conn, $_POST['perkara']);
    $kolej = mysqli_real_escape_string($conn, $_POST['kolej']);
    $target_role = mysqli_real_escape_string($conn, $_POST['target_role']);
    
    // 2. Dapatkan Emel Penerima
    $stmt_email = $conn->prepare("SELECT email FROM users WHERE role = ? LIMIT 1");
    $stmt_email->bind_param("s", $target_role);
    $stmt_email->execute();
    $result = $stmt_email->get_result();
    $email_penerima = ($result->num_rows > 0) ? $result->fetch_assoc()['email'] : null;
    
    if (!$email_penerima) die("Ralat: Tiada emel untuk role $target_role");

    // 3. Proses Fail (Upload ke Server & Google Drive)
    if (isset($_FILES['fail_surat']) && $_FILES['fail_surat']['error'] == 0) {
        $file_name = time() . '_' . $_FILES['fail_surat']['name'];
        $target_dir = "uploads/";
        $file_path = $target_dir . $file_name;
        
        // Simpan ke folder tempatan
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        move_uploaded_file($_FILES['fail_surat']['tmp_name'], $file_path);

        // Hantar ke Google Drive
        $base64_file = base64_encode(file_get_contents($file_path));
        $payload = json_encode(['fileData' => $base64_file, 'mimeType' => 'application/pdf', 'fileName' => $file_name]);
        
        $ch_drive = curl_init("https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec");
        curl_setopt($ch_drive, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch_drive, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_drive, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $drive_response = trim(curl_exec($ch_drive));
        $http_code_drive = curl_getinfo($ch_drive, CURLINFO_HTTP_CODE);
        curl_close($ch_drive);

        $drive_file_id = ($http_code_drive == 200 && strlen($drive_response) < 200) ? $drive_response : "GAGAL_UPLOAD";
    } else {
        die("Fail tidak dijumpai atau ralat muat naik.");
    }

    // 4. Integrasi API Brevo (E-mel)
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

    // 5. Simpan ke Database
    $stmt = $conn->prepare("INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, target_role, status, drive_file_id, fail_surat) VALUES (?, ?, ?, ?, ?, ?, 'BARU', ?, ?)");
    $stmt->bind_param("ssssssss", $no_rujukan, $tarikh_terima, $daripada, $perkara, $kolej, $target_role, $drive_file_id, $file_name);
    
    if ($stmt->execute()) {
        echo "<script>alert('Berjaya dihantar ke Drive & Server!'); window.location='homeadmin.php';</script>";
    } else {
        echo "Ralat Database: " . $stmt->error;
    }
}
?>
