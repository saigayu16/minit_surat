<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
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
    
    // Ambil emel penerima
    $stmt_email = $conn->prepare("SELECT email FROM users WHERE role = ? LIMIT 1");
    $stmt_email->bind_param("s", $target_role);
    $stmt_email->execute();
    $result = $stmt_email->get_result();
    
    if ($result->num_rows > 0) {
        $email_penerima = $result->fetch_assoc()['email'];
    } else {
        echo "<script>alert('Ralat: Tiada emel untuk role $target_role'); window.history.back();</script>";
        exit;
    }

    // Proses fail
    if (isset($_FILES['fail_surat']) && $_FILES['fail_surat']['error'] == 0) {
        $file_name = $_FILES['fail_surat']['name'];
        $file_tmp = $_FILES['fail_surat']['tmp_name'];
        $file_data = file_get_contents($file_tmp);
        $base64_file = base64_encode($file_data);

        // --- TAMBAHAN: HANTAR KE GOOGLE DRIVE ---
        $google_script_url = "https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec"; 
        $payload = json_encode(['fileData' => $base64_file, 'mimeType' => 'application/pdf', 'fileName' => $file_name]);
        
        $ch_drive = curl_init($google_script_url);
        curl_setopt($ch_drive, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch_drive, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_drive, CURLOPT_FOLLOWLOCATION, true);
        $drive_file_id = curl_exec($ch_drive);
        curl_close($ch_drive);
        // ----------------------------------------
    } else {
        echo "<script>alert('Fail diperlukan.'); window.history.back();</script>";
        exit;
    }

    // Integrasi API Brevo
    $api_key = getenv('BREVO_API_KEY');
    $data = [
        "sender" => ["email" => "saigayu1605@gmail.com", "name" => "Sistem Minit Digital"],
        "to" => [["email" => $email_penerima]],
        "subject" => "Notifikasi: Surat Baharu - " . $no_rujukan,
        "htmlContent" => "Assalamualaikum, terdapat surat baharu untuk tindakan anda.",
        "attachment" => [["content" => $base64_file, "name" => $file_name]]
    ];

    // --- TAMBAHAN: HANTAR KE GOOGLE DRIVE ---
$google_script_url = "https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec"; 
$payload = json_encode(['fileData' => $base64_file, 'mimeType' => 'application/pdf', 'fileName' => $file_name]);

$ch_drive = curl_init($google_script_url);
curl_setopt($ch_drive, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch_drive, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_drive, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch_drive, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); // Penting!
$drive_response = curl_exec($ch_drive);
$http_code_drive = curl_getinfo($ch_drive, CURLINFO_HTTP_CODE);
curl_close($ch_drive);

// Pastikan respon hanya ID (biasanya 30-40 aksara)
if ($http_code_drive == 200) {
    $drive_file_id = trim($drive_response); 
} else {
    // Jika gagal, jangan simpan mesej ralat ke DB untuk elak "Data too long"
    $drive_file_id = "GAGAL_UPLOAD"; 
}
}
?>
