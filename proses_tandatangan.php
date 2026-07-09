<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $catatan = isset($_POST['catatan']) ? $_POST['catatan'] : '';
    $arahan = isset($_POST['arahan_pilihan']) ? $_POST['arahan_pilihan'] : '';
    $fileId = isset($_POST['fileId']) ? $_POST['fileId'] : '';

    // 1. Update Database
    $stmt = $conn->prepare("UPDATE minit_surat SET status = 'SELESAI', catatan = ?, arahan = ? WHERE id = ?");
    if (!$stmt) {
        die("Ralat SQL: " . $conn->error);
    }
    $stmt->bind_param("ssi", $catatan, $arahan, $id);
    
    if (!$stmt->execute()) {
        die("Gagal Update Database: " . $stmt->error);
    }

    // 2. Hantar ke Google Apps Script
    $webAppUrl = 'https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec'; 
    $data = [
        'id' => $id,
        'fileId' => $fileId,
        'folderId' => "1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1"
    ];

    $ch = curl_init($webAppUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Tambahan penting jika URL ada redirect
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "Ralat CURL: " . $error;
    } else {
        echo 'success';
    }
}
?>
