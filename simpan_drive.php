<?php
$folder_id = "1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1";
$id_dokumen = $_POST['id'] ?? 'tiada_id';

// Pastikan data wujud
if (!isset($_POST['fail_baharu_base64'])) {
    echo json_encode(['status' => 'error', 'message' => 'Tiada data diterima']);
    exit;
}

$web_url = "https://script.google.com/macros/s/AKfycbzC-iMS5JwBfSa-GgrVBWaoLo1jxr-PJ6VxIqUP1QMGfKe0b_8ur1QcMwQZAJcXD7Sm/exec";

$data = [
    'folder_id' => $folder_id,
    'nama_fail' => 'Minit_Disahkan_' . $id_dokumen . '.pdf',
    'fail_base64' => $_POST['fail_baharu_base64']
];

$ch = curl_init($web_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Guna http_build_query untuk POST form
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$respon = curl_exec($ch);
curl_close($ch);

echo $respon;
?>
