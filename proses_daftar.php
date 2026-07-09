<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil input
    $no_rujukan = mysqli_real_escape_string($conn, $_POST['no_rujukan']);
    $tarikh_terima = mysqli_real_escape_string($conn, $_POST['tarikh_terima']);
    $daripada = mysqli_real_escape_string($conn, $_POST['daripada']);
    $perkara = mysqli_real_escape_string($conn, $_POST['perkara']);
    $kolej = mysqli_real_escape_string($conn, $_POST['kolej']);
    $target_role = mysqli_real_escape_string($conn, $_POST['target_role']);
    
    // 2. Ambil emel
    $stmt_email = $conn->prepare("SELECT email FROM users WHERE role = ? LIMIT 1");
    $stmt_email->bind_param("s", $target_role);
    $stmt_email->execute();
    $result = $stmt_email->get_result();
    
    if ($result->num_rows > 0) {
        $email_penerima = $result->fetch_assoc()['email'];
    } else {
        die("Ralat: Tiada emel untuk role $target_role");
    }

    // 3. Proses Fail & Hantar ke Drive (Hanya SEKALI)
    if (isset($_FILES['fail_surat']) && $_FILES['fail_surat']['error'] == 0) {
        $file_name = $_FILES['fail_surat']['name'];
        $file_data = file_get_contents($_FILES['fail_surat']['tmp_name']);
        $base64_file = base64_encode($file_data);

        $google_script_url = "https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec";
        $payload = json_encode(['fileData' => $base64_file, 'mimeType' => 'application/pdf', 'fileName' => $file_name]);
        
        $ch_drive = curl_init($google_script_url);
        curl_setopt($ch_drive, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch_drive, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_drive, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $drive_response = curl_exec($ch_drive);
        $http_code_drive = curl_getinfo($ch_drive, CURLINFO_HTTP_CODE);
        curl_close($ch_drive);

        $drive_file_id = ($http_code_drive == 200) ? trim($drive_response) : "GAGAL_UPLOAD";
    } else {
        die("Fail diperlukan.");
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
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 5. Simpan ke Database
    if ($http_code == 201) {
        $stmt = $conn->prepare("INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, target_role, status, drive_file_id) VALUES (?, ?, ?, ?, ?, ?, 'BARU', ?)");
        $stmt->bind_param("sssssss", $no_rujukan, $tarikh_terima, $daripada, $perkara, $kolej, $target_role, $drive_file_id);
        
        if ($stmt->execute()) {
            echo "<script>alert('Berjaya dihantar!'); window.location='homeadmin.php';</script>";
        } else {
            echo "Ralat Database: " . $stmt->error;
        }
    } else {
        echo "E-mel gagal (Ralat: $http_code)";
    }
}
?>
