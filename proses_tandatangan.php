<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // URL Web App Google
    $webAppUrl = 'https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec'; 

    $ch = curl_init($webAppUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($_POST));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    echo ($response === 'SUCCESS') ? 'success' : 'Ralat: ' . $response;
    curl_close($ch);

    $sql = "UPDATE minit_surat SET status = 'SELESAI', catatan_pengarah = ?, arahan_pengarah = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $_POST['catatan'], $_POST['arahan_pilihan'], $id);
    $stmt->execute();
    }
?>
