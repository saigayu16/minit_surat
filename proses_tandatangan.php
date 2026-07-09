<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil data POST
    $id = intval($_POST['id']);
    $catatan = isset($_POST['catatan']) ? mysqli_real_escape_string($conn, $_POST['catatan']) : '';
    $arahan = isset($_POST['arahan_pilihan']) ? mysqli_real_escape_string($conn, $_POST['arahan_pilihan']) : '';

    // 2. Hantar ke Google Apps Script (Curl)
    $webAppUrl = 'https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec'; 
    $ch = curl_init($webAppUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($_POST));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch); // Hasil dari Google (SUCCESS)
    curl_close($ch);

    // 3. SELEPAS CURL SELESAI, baru kita buat update database di sini:
    // (Ganti bahagian if ($response == 'SUCCESS') dengan ini)
    
    $stmt = $conn->prepare("UPDATE minit_surat SET status = 'SELESAI', catatan_pengarah = ?, arahan_pengarah = ? WHERE id = ?");
    $stmt->bind_param("ssi", $catatan, $arahan, $id);
    
    if ($stmt->execute()) {
        // Jika database berjaya update
        echo 'success'; 
    } else {
        // Jika database gagal update
        echo 'Ralat Database: ' . $stmt->error;
    }
}
?>
