<?php
// Fail ini dipanggil untuk menghantar e-mel selepas surat didaftarkan
$api_key = getenv('BREVO_API_KEY');

// Pastikan data penting diterima
$email_penerima = $_POST['email_penerima'] ?? '';
$no_rujukan     = $_POST['no_rujukan'] ?? 'Tiada Rujukan';
$base64_file    = $_POST['base64_file'] ?? ''; // Jika fail dihantar
$file_name      = $_POST['file_name'] ?? '';

if (empty($email_penerima)) {
    die("Ralat: Tiada alamat e-mel penerima.");
}

$data = [
    "sender" => ["email" => "saigayu1605@gmail.com", "name" => "Sistem Minit Digital"],
    "to" => [["email" => $email_penerima]],
    "subject" => "Notifikasi: Surat Baharu - " . $no_rujukan,
    "htmlContent" => "Assalamualaikum, terdapat surat baharu untuk tindakan anda."
];

// Tambah lampiran jika ada
if (!empty($base64_file)) {
    $data["attachment"] = [["content" => $base64_file, "name" => $file_name]];
}

$ch = curl_init('https://api.brevo.com/v3/smtp/email');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['api-key: ' . $api_key, 'Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Semakan Ralat (Penting!)
if ($http_code != 200 && $http_code != 201) {
    echo "Ralat Brevo (HTTP $http_code): " . $response;
} else {
    echo "E-mel berjaya dihantar!";
}
?>
