<?php
include('db.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // 1. Ambil ID fail dari Google Drive, BUKAN blob fail_surat
    $stmt = $conn->prepare("SELECT drive_file_id FROM minit_surat WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && !empty($row['drive_file_id'])) {
        $fileId = $row['drive_file_id'];
        
        // 2. Redirect ke Google Drive Preview (Cara paling selamat & mudah)
        header("Location: https://drive.google.com/file/d/$fileId/preview");
        exit;
    }
}

header("HTTP/1.0 404 Not Found");
echo "Dokumen tidak dijumpai atau ID fail tidak sah.";
?>
