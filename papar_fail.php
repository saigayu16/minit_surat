<?php
include('db.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT fail_surat FROM minit_surat WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && !empty($row['fail_surat'])) {
        // Penting: Tambah header ini supaya pelayar tahu ia adalah fail yang boleh dimuat turun/papar
        header("Content-Type: application/pdf");
        header("Content-Length: " . strlen($row['fail_surat']));
        header("Content-Disposition: inline; filename=dokumen_minit.pdf");
        header("Cache-Control: private, max-age=0, must-revalidate");
        header("Pragma: public");
        
        echo $row['fail_surat'];
        exit;
    }
}

// Jika tiada fail atau ralat
header("HTTP/1.0 404 Not Found");
echo "Dokumen tidak dijumpai dalam sistem.";
?>
