<?php
include('db.php');
$id = intval($_GET['id']);

// Dapatkan ID fail dari database
$res = $conn->query("SELECT drive_file_id FROM minit_surat WHERE id = $id");
$row = $res->fetch_assoc();
$fileId = $row['drive_file_id'];

// Redirect ke Google Drive Preview (Cara paling mudah)
if ($fileId) {
    header("Location: https://drive.google.com/file/d/$fileId/preview");
} else {
    echo "Fail tidak dijumpai dalam Drive.";
}
?>
