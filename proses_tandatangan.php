<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $catatan = $_POST['catatan'];
    $arahan = $_POST['arahan_pilihan'];
    $image = $_POST['image'];

    // Update ke database, status tukar kepada 'DISAHKAN'
    $stmt = $conn->prepare("UPDATE minit_surat SET 
                            status = 'DISAHKAN', 
                            catatan = ?, 
                            arahan_pilihan = ?, 
                            tandatangan = ? 
                            WHERE id = ?");
    
    $stmt->bind_param("sssi", $catatan, $arahan, $image, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo $conn->error;
    }
}
?>
