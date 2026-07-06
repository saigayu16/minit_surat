<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

// 1. Ganti dengan URL Web App Google Apps Script anda
$webAppUrl = 'https://script.google.com/macros/s/AKfycbxFL9V5-bXoDKlinxm-n5KT1IeTmh0Uposh4vgTdKbKQX-9bwkQe5Vs8zZJtRIzvzKy/exec';

$payload = json_encode([
    "image" => str_replace('data:image/png;base64,', '', $input['image']),
    "fileName" => $input['nama_fail'],
    "folderId" => '1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1' // 2. Ganti dengan ID Folder Drive anda
]);

$ch = curl_init($webAppUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>