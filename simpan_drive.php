<?php
// Pastikan anda menerima data JSON
$input = json_decode(file_get_contents('php://input'), true);

// TAMPAL URL ANDA DI SINI (Contoh: https://script.google.com/...)
$webAppUrl = 'https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec'; 

$payload = json_encode([
    "image" => $input['image'], // Base64 dari canvas
    "fileName" => $input['nama_fail'],
    "folderId" => '1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1'
]);

$ch = curl_init($webAppUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

echo $response; // Ini akan memberi maklum balas "SUCCESS" atau "ERROR" pada skrin anda
?>
