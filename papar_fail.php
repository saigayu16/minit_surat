<?php
include('db.php');
$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT drive_file_id FROM minit_surat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row && !empty($row['drive_file_id']) && $row['drive_file_id'] !== "GAGAL_UPLOAD") {
    // Hanya cetak URL tanpa header redirect
    echo "https://drive.google.com/file/d/" . trim($row['drive_file_id']) . "/preview";
} else {
    echo "ERROR";
}
?>
