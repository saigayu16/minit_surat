<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $catatan = $_POST['catatan'];
    $arahan = $_POST['arahan_pilihan'];

    // Update Database
    $stmt = $conn->prepare("UPDATE minit_surat SET status = 'SELESAI', catatan_pengarah = ?, arahan_pengarah = ? WHERE id = ?");
    $stmt->bind_param("ssi", $catatan, $arahan, $id);
    $stmt->execute();

    // Hantar ke Google
    $webAppUrl = 'https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec'; 
    $ch = curl_init($webAppUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
    curl_exec($ch);
    curl_close($ch);

    echo 'success';
}
?>
