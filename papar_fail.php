<?php
include('db.php');
$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT drive_file_id FROM minit_surat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row && !empty($row['drive_file_id']) && $row['drive_file_id'] !== "GAGAL_UPLOAD") {
    $fileId = trim($row['drive_file_id']);
    // Papar terus melalui iframe Google Drive
    header("Location: https://drive.google.com/file/d/$fileId/preview");
    exit;
} else {
    echo "<h1>Fail tidak dijumpai dalam Drive.</h1>";
    echo "<p>ID Fail dalam database: " . ($row['drive_file_id'] ?? 'Tiada') . "</p>";
}
?>
