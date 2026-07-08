<?php
session_start();
include('db.php');

if (!isset($_POST['id']) || !isset($_POST['image'])) {
    die("error: Data tidak lengkap.");
}

$id = intval($_POST['id']);
$dataURL = $_POST['image'];
$catatan = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');
$arahan = mysqli_real_escape_string($conn, $_POST['arahan_pilihan'] ?? '');

// 1. Ambil data surat
$stmt = $conn->prepare("SELECT no_rujukan, fail_surat FROM minit_surat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$surat = $stmt->get_result()->fetch_assoc();

if (!$surat) die("error: Dokumen tidak ditemui.");

// 2. Proses Imej Tandatangan (Tukar ke Binary untuk simpan dalam DB)
$parts = explode(',', $dataURL);
$bin_tandatangan = base64_decode($parts[1]);

// 3. Integrasi Google Drive
// Nota: Pastikan fail asal 'fail_surat' juga diambil dari DB, bukan dari path fizikal
$file_content = base64_encode($surat['fail_surat']); // Contoh jika fail disimpan sebagai BLOB
$payload = json_encode([
    "image" => $file_content,
    "fileName" => 'DISAHKAN_' . $surat['no_rujukan'] . '.pdf',
    "folderId" => '1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1'
]);

$ch = curl_init('https://script.google.com/macros/s/AKfycbzarpp5n2MvEZbGGx9mWZI6isU6VHwEWGmTBhXDO1nC__QrFwyxgHYgaq3biqiZ0fj9/exec');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_exec($ch);
curl_close($ch);

// 4. Kemaskini Database (Gunakan Prepared Statement untuk keselamatan)
$sql = "UPDATE minit_surat SET tandatangan_data=?, catatan=?, arahan_pilihan=?, status='SELESAI' WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $bin_tandatangan, $catatan, $arahan, $id);

if ($stmt->execute()) {
    $role = $_SESSION['user_role'] ?? '';
    $redirects = ['pengarah' => 'homedirector.php', 'tpa' => 'hometpa.php', 'tpp' => 'hometpp.php'];
    echo $redirects[$role] ?? 'index.php';
} else {
    echo "error";
}
?>
