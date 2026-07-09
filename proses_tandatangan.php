<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil data dari POST
    $id = intval($_POST['id']);
    // Pastikan nama kolum 'catatan_pengarah' dan 'arahan_pengarah' wujud dalam table anda
    $catatan = isset($_POST['catatan']) ? mysqli_real_escape_string($conn, $_POST['catatan']) : '';
    $arahan = isset($_POST['arahan_pilihan']) ? mysqli_real_escape_string($conn, $_POST['arahan_pilihan']) : '';

    // 2. Kemas kini Database kepada SELESAI
    // INI ADALAH BAHAGIAN PENTING YANG AKAN TUKAR BUTANG DI HOMEDIRECTOR
    $stmt = $conn->prepare("UPDATE minit_surat SET status = 'SELESAI', catatan_pengarah = ?, arahan_pengarah = ? WHERE id = ?");
    $stmt->bind_param("ssi", $catatan, $arahan, $id);
    
    if ($stmt->execute()) {
        // 3. Jika DB berjaya update, barulah kita hantar ke Google Drive (Kod asal anda)
        $webAppUrl = 'https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec'; 
        
        $ch = curl_init($webAppUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($_POST));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        curl_close($ch);

        // Beri respon 'success' kepada Javascript tandatangan.php
        echo 'success'; 
    } else {
        echo 'Ralat Database: ' . $stmt->error;
    }
}
?>
