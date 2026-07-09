<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $catatan = $_POST['catatan'];
    $arahan = $_POST['arahan_pilihan'];

    // 1. Update Database (Gunakan nama kolum yang anda ada, contoh: catatan, arahan)
    $stmt = $conn->prepare("UPDATE minit_surat SET status = 'SELESAI', catatan = ?, arahan = ? WHERE id = ?");
    $stmt->bind_param("ssi", $catatan, $arahan, $id);
    $stmt->execute();

    // 2. Hantar hanya data ringkas ke Google Apps Script
    $webAppUrl = 'https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec'; 
    $data = [
        'id' => $id,
        'fileId' => $_POST['fileId'], // Ambil terus dari database yang dihantar JS
        'folderId' => "1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1"
    ];

    $ch = curl_init($webAppUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_exec($ch);
    curl_close($ch);

    echo 'success';
}
?>
