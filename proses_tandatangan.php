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

// 1. Ambil data surat untuk rujukan Drive
$query = $conn->query("SELECT no_rujukan, fail_surat FROM minit_surat WHERE id = $id");
$surat = $query->fetch_assoc();
if (!$surat) die("error: Dokumen tidak ditemui.");

// 2. Simpan imej tandatangan tempatan
$parts = explode(',', $dataURL);
$nama_fail_tandatangan = 'ttd_' . time() . '_' . $id . '.png';
if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }
file_put_contents('uploads/' . $nama_fail_tandatangan, base64_decode($parts[1]));

// 3. Integrasi Google Drive (Hantar PDF Asal)
$path_fail_asal = 'uploads/' . $surat['fail_surat']; 
if (file_exists($path_fail_asal)) {
    $file_content = base64_encode(file_get_contents($path_fail_asal));
    $payload = json_encode([
        "image" => $file_content,
        "fileName" => 'DISAHKAN_' . $surat['fail_surat'],
        "folderId" => '1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1'
    ]);

    $ch = curl_init('https://script.google.com/macros/s/AKfycbzarpp5n2MvEZbGGx9mWZI6isU6VHwEWGmTBhXDO1nC__QrFwyxgHYgaq3biqiZ0fj9/exec');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}

// 4. Kemaskini Database
$sql = "UPDATE minit_surat SET tandatangan_fail='$nama_fail_tandatangan', catatan='$catatan', arahan_pilihan='$arahan', status='SELESAI' WHERE id=$id";

if ($conn->query($sql) === TRUE) {
    // Redirect Dinamik
    $role = $_SESSION['user_role'] ?? '';
    if ($role == 'pengarah') echo "homedirector.php";
    elseif ($role == 'tpa') echo "hometpa.php";
    elseif ($role == 'tpp') echo "hometpp.php";
    else echo "index.php";
} else {
    echo "error";
}
?>