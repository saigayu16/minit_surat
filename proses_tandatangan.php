<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $webAppUrl = 'https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec'; 

    $payload = [
        "id" => $_POST['id'],
        "image" => $_POST['image'],
        "fileId" => $_POST['fileId'],
        "catatan" => $_POST['catatan'],
        "arahan" => $_POST['arahan_pilihan'],
        "folderId" => '1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1'
    ];

    $ch = curl_init($webAppUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // TAMBAH INI: Membenarkan redirect Google
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    
    // Semak jika ada error cURL
    if (curl_errno($ch)) {
        echo 'Error cURL: ' . curl_error($ch);
    } else {
        echo (trim($response) === 'SUCCESS') ? 'success' : 'Ralat Google: ' . $response;
    }
    
    curl_close($ch);
}
?>
