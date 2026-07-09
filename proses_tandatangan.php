<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // URL Web App Google (PASTIKAN DITAMBAH /exec DI HUJUNG)
    $webAppUrl = 'https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec'; 

    $payload = [
        "id" => $_POST['id'],
        "image" => $_POST['image'],
        "fileId" => $_POST['fileId'], // ID fail PDF asal
        "catatan" => $_POST['catatan'],
        "arahan" => $_POST['arahan_pilihan'],
        "folderId" => '1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1'
    ];

    $ch = curl_init($webAppUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    curl_close($ch);

    echo (trim($response) === 'SUCCESS') ? 'success' : 'Gagal: ' . $response;
}
?>
