<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

$webAppUrl = 'https://script.google.com/macros/s/AKfycbwy4aQdfAN3M_72NdFQCFosvM9DAe74BIJaRSr4-0orntxrBV-0JdOqaF4qMg_aBUU_/exec';

$payload = json_encode([
    "image" => str_replace('data:image/png;base64,', '', $input['image']),
    "fileName" => $input['nama_fail'],
    "folderId" => '1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1'
]);

$ch = curl_init($webAppUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Tambahkan ini supaya ia ikut redirect Google
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);

// TAMBAHAN: Semak ralat cURL dan tunjukkan respon
if (curl_errno($ch)) {
    echo json_encode(["status" => "error", "message" => "cURL Error: " . curl_error($ch)]);
} else {
    echo $response; // Ini akan memaparkan apa yang Google jawab
}

curl_close($ch);
?>
