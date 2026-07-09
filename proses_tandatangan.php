<?php
// proses_tandatangan.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // URL Web App Google anda (Pastikan ada /exec di hujung)
    $webAppUrl = 'https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec'; 

    $payload = [
        "image" => $_POST['image'], // Tandatangan (Base64)
        "fileName" => "Tandatangan_ID_" . $_POST['id'] . ".png",
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

    // Hantar respons kembali kepada JavaScript anda
    if (strpos($response, 'SUCCESS') !== false) {
        echo 'success';
    } else {
        echo 'Gagal menyimpan ke Drive: ' . $response;
    }
}
?>
