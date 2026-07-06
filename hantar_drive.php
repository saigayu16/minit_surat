<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Panggil fail db.php dari folder utama projek
include('db.php');

if (!isset($_SESSION['user_logged_in'])) { 
    echo "ERROR_SESSION"; 
    exit; 
}

// 1. Semak sama ada fail PDF bercop dihantar dengan betul melalui FormData
if (!isset($_FILES['fail_pdf_bercop']) || $_FILES['fail_pdf_bercop']['error'] !== UPLOAD_ERR_OK) {
    echo "INVALID_REQUEST";
    exit;
}

// BARIS 21 - TELAH DIPERBETULKAN 
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$path_fail_sementara = $_FILES['fail_pdf_bercop']['tmp_name'];

if ($id === 0) {
    echo "INVALID_REQUEST";
    exit;
}

// Ambil no rujukan untuk nama fail di Drive
$query = $conn->query("SELECT no_rujukan FROM minit_surat WHERE id = $id");
$data = $query->fetch_assoc();
$no_rujukan = $data ? $data['no_rujukan'] : 'SURAT';

$nama_baru_drive = 'MINIT_DISAHKAN_' . str_replace('/', '-', $no_rujukan) . '_' . $id . '.pdf';
$google_folder_id = '1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1'; 

try {
    // 2. Baca kandungan fail PDF bercop dari path sementaranya (temporary path)
    $file_content = base64_encode(file_get_contents($path_fail_sementara));
    $webAppUrl = 'https://script.google.com/macros/s/AKfycbxEAE40VA_Qnw-r9aXmiexlSnZQtbvsP8gFvXU_TjWHNA23htmeYDMRljogvULQim5g/exec';

    $payload = json_encode([
        "image" => $file_content, 
        "fileName" => $nama_baru_drive,
        "folderId" => $google_folder_id 
    ]);

    $ch = curl_init($webAppUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    if (isset($result['status']) && $result['status'] === 'success') {
        echo "DRIVE_SUCCESS";
    } else {
        echo "DRIVE_FAILED";
    }
} catch (Exception $e) {
    echo "CATCH_ERROR";
}
?>