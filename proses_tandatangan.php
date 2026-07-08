<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $catatan = $_POST['catatan'] ?? '';
    $arahan = $_POST['arahan_pilihan'] ?? '';
    $dataURL = $_POST['image'];

    // Convert base64 kepada binary
    $parts = explode(',', $dataURL);
    $bin_tandatangan = base64_decode($parts[1]);

    // Update database
    $stmt = $conn->prepare("UPDATE minit_surat SET tandatangan_data=?, catatan=?, arahan_pilihan=?, status='SELESAI' WHERE id=?");
    
    if (!$stmt) {
        die("Ralat Database: " . $conn->error);
    }

    $stmt->bind_param("sssi", $bin_tandatangan, $catatan, $arahan, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Ralat Semasa Menyimpan: " . $stmt->error;
    }
}
?>
