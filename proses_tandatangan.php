<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');
    $arahan = mysqli_real_escape_string($conn, $_POST['arahan_pilihan'] ?? '');
    $dataURL = $_POST['image'];

    // Tukar base64 imej kepada binary untuk LONGBLOB
    $parts = explode(',', $dataURL);
    $bin_ttd = base64_decode($parts[1]);

    // Update database
    $stmt = $conn->prepare("UPDATE minit_surat SET tandatangan_data=?, catatan=?, arahan_pilihan=?, status='SELESAI' WHERE id=?");
    $stmt->bind_param("sssi", $bin_ttd, $catatan, $arahan, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Ralat Database: " . $stmt->error;
    }
}
?>
