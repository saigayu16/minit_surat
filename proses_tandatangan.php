<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Dapatkan data daripada AJAX
    $id = intval($_POST['id']);
    $catatan = isset($_POST['catatan']) ? $_POST['catatan'] : '';
    $arahan = isset($_POST['arahan_pilihan']) ? $_POST['arahan_pilihan'] : '';
    
    // 2. Update database (Pastikan nama kolum sama dengan database anda)
    // Saya telah tambah tandatangan_fail jika anda ingin simpan nama fail di sini juga
    $stmt = $conn->prepare("UPDATE minit_surat SET status = 'SELESAI', catatan = ?, arahan_pilihan = ?, tarikh_sah = NOW() WHERE id = ?");
    
    if (!$stmt) {
        die("Ralat SQL: " . $conn->error);
    }
    
    $stmt->bind_param("ssi", $catatan, $arahan, $id);
    
    if ($stmt->execute()) {
        // 3. Integrasi Google Apps Script (Jika perlu)
        // Pastikan URL Google Apps Script anda adalah betul
        $webAppUrl = 'https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec'; 
        
        $data = [
            'id' => $id,
            'folderId' => "1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1"
        ];

        $ch = curl_init($webAppUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        
        $response = curl_exec($ch);
        curl_close($ch);

        // Beri maklum balas kepada AJAX
        echo "success";
    } else {
        echo "Gagal: " . $stmt->error;
    }
}
?>
