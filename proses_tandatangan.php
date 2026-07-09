<?php
// proses_tandatangan.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $webAppUrl = 'https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec'; // Ganti dengan URL Google Apps Script

    $payload = [
        "id" => $_POST['id'],
        "image" => $_POST['image'], // Data base64 tandatangan
        "catatan" => $_POST['catatan'],
        "folderId" => '1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1'
    ];

    $ch = curl_init($webAppUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    curl_close($ch);

    echo ($response === 'SUCCESS') ? 'success' : 'Gagal: ' . $response;
}
?>
