<?php
// Minit_proses.php
header('Content-Type: application/json');

$folder_id = "1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1";
$id_dokumen = $_POST['id'] ?? 'tiada_id';
$base64_data = $_POST['fail_baharu_base64'] ?? '';

if (empty($base64_data)) {
    echo json_encode(['status' => 'error', 'message' => 'Tiada data fail diterima']);
    exit;
}

// URL Skrip Google anda (Gantikan dengan URL sebenar)
$web_url = "https://script.google.com/macros/s/AKfycbzC-iMS5JwBfSa-GgrVBWaoLo1jxr-PJ6VxIqUP1QMGfKe0b_8ur1QcMwQZAJcXD7Sm/exec";

// Data yang dihantar ke Google
$data = [
    'folder_id' => $folder_id,
    'nama_fail' => 'Minit_Disahkan_' . $id_dokumen . '.pdf',
    'fail_base64' => $base64_data,
    'id' => $id_dokumen
];

$ch = curl_init($web_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$respon = curl_exec($ch);
curl_close($ch);

echo $respon;
?>
