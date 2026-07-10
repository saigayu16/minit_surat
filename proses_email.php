<?php
// Pastikan anda mempunyai akses kepada BREVO_API_KEY dalam environment variables
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil data dari form
    $id = $_POST['surat_id'];
    $email = $_POST['email'];
    $nama_staf = $_POST['nama_staf'];

    // 2. Semakan staf
    $stmt_check = $conn->prepare("SELECT nama FROM staff WHERE email = ? AND nama = ?");
    $stmt_check->bind_param("ss", $email, $nama_staf);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        die("<script>alert('Ralat: Maklumat staf tidak sah!'); window.history.back();</script>");
    }

    // 3. Proses Fail ke Google Drive (Sama konsep seperti proses_daftar)
    $drive_file_id = "GAGAL_UPLOAD";
    $base64_file = "";
    $file_name = "";

    if (isset($_FILES['dokumen_minit']) && $_FILES['dokumen_minit']['error'] == 0) {
        $file_name = $_FILES['dokumen_minit']['name'];
        $base64_file = base64_encode(file_get_contents($_FILES['dokumen_minit']['tmp_name']));
        $payload = json_encode(['fileData' => $base64_file, 'mimeType' => 'application/pdf', 'fileName' => $file_name]);
        
        $ch_drive = curl_init("https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec");
        curl_setopt($ch_drive, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch_drive, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_drive, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch_drive, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $drive_response = trim(curl_exec($ch_drive));
        curl_close($ch_drive);
        $drive_file_id = $drive_response;
    }

    // 4. Integrasi API Brevo (E-mel)
    $api_key = getenv('BREVO_API_KEY'); // Pastikan key ini sudah diset di Render
    $data = [
        "sender" => ["email" => "saigayu1605@gmail.com", "name" => "Sistem Minit Digital"],
        "to" => [["email" => $email]],
        "subject" => "Notifikasi Minit Surat",
        "htmlContent" => "Hai <strong>$nama_staf</strong>,<br><br>Anda telah dimaklumkan mengenai surat ini. Sila rujuk dokumen minit yang dilampirkan.<br><br>Terima kasih.",
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

    // 5. Kemaskini Database
    if ($http_code >= 200 && $http_code < 300) {
        $stmt = $conn->prepare("UPDATE minit_surat SET status = 'DIMAKLUM', maklum_kepada = ?, drive_file_id = ? WHERE id = ?");
        $stmt->bind_param("sss", $nama_staf, $drive_file_id, $id);
        $stmt->execute();
        echo "<script>alert('E-mel berjaya dihantar!'); window.location='homeadmin.php';</script>";
    } else {
        echo "<script>alert('Gagal menghantar e-mel melalui Brevo. Kod: $http_code'); window.history.back();</script>";
    }
}
?>
