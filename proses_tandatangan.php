<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Dapatkan data daripada AJAX
    $id = intval($_POST['id']);
    $catatan = isset($_POST['catatan']) ? $_POST['catatan'] : '';
    $arahan = isset($_POST['arahan_pilihan']) ? $_POST['arahan_pilihan'] : '';
    $image_data = isset($_POST['image']) ? $_POST['image'] : ''; // Data Base64 tandatangan
    
    // 2. Update database
    // Pastikan column 'tandatangan' wujud di dalam table 'minit_surat' anda (jenis LONGTEXT)
    $stmt = $conn->prepare("UPDATE minit_surat SET 
                            status = 'SELESAI', 
                            catatan = ?, 
                            arahan_pilihan = ?, 
                            tandatangan = ?, 
                            tarikh_sah = NOW() 
                            WHERE id = ?");
    
    if (!$stmt) {
        die("Ralat SQL: " . $conn->error);
    }
    
    $stmt->bind_param("sssi", $catatan, $arahan, $image_data, $id);
    
    if ($stmt->execute()) {
        // 3. Integrasi Google Apps Script
        // Kita hantar data ke Google Apps Script (termasuk tandatangan jika Apps Script anda perlukannya)
        $webAppUrl = 'https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec'; 
        
        $data = [
            'id' => $id,
            'folderId' => "1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1"
            // Jika Apps Script anda perlu data tandatangan, anda boleh tambah: 'image' => $image_data
        ];

        $ch = curl_init($webAppUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        
        $response = curl_exec($ch);
        curl_close($ch);

        // 4. Beri maklum balas kepada AJAX
        // Kita pulangkan URL untuk redirect
        echo "homedirector.php?id=" . $id; 
    } else {
        echo "error";
    }
}
?>
