<?php
session_start();
include('db.php'); // Pastikan db.php menggunakan connection PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil data daripada POST
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $catatan = isset($_POST['catatan']) ? $_POST['catatan'] : '';
    $arahan = isset($_POST['arahan_pilihan']) ? $_POST['arahan_pilihan'] : '';
    $image = isset($_POST['image']) ? $_POST['image'] : '';

    if ($id === 0) {
        echo "Ralat: ID tidak sah";
        exit;
    }

    try {
        // 2. Gunakan PDO prepare & execute (Sesuai untuk PostgreSQL/Neon)
        // Nota: Pastikan nama column dalam DB sama dengan 'catatan', 'arahan_pilihan', 'tandatangan'
        $sql = "UPDATE minit_surat SET 
                status = 'SELESAI TANDATANGAN', 
                catatan = ?, 
                arahan_pilihan = ?, 
                tandatangan = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        
        // 3. Eksekusi query
        if ($stmt->execute([$catatan, $arahan, $image, $id])) {
            echo "success";
        } else {
            // Dapatkan info ralat jika gagal
            $error = $stmt->errorInfo();
            echo "Ralat Database: " . $error[2];
        }
    } catch (PDOException $e) {
        echo "Ralat Sistem: " . $e->getMessage();
    }
}
?>
