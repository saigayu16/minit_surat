<?php
include('db.php');

$id = $_POST['id'];
$img = $_POST['image'];

// 1. Bersihkan data base64
$img = str_replace('data:image/png;base64,', '', $img);
$img = str_replace(' ', '+', $img);
$data = base64_decode($img);

// 2. Tentukan nama fail dan lokasi
$nama_fail = 'sig_' . $id . '_' . time() . '.png';
$fail_path = 'uploads/' . $nama_fail;

// 3. Simpan fail ke folder uploads
file_put_contents($fail_path, $data);

// 4. Update database (Pastikan kolum 'tandatangan_fail' wujud)
$stmt = $conn->prepare("UPDATE minit_surat SET tandatangan_fail = ?, status = 'SELESAI', tarikh_sah = NOW() WHERE id = ?");
$stmt->bind_param("si", $nama_fail, $id);
$stmt->execute();

echo "Success";
?>
