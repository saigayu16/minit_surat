<?php
session_start();
include('db.php');

if (!isset($_POST['id']) || !isset($_POST['image'])) {
    die("error: Data tidak lengkap.");
}

$id = intval($_POST['id']);
$catatan = $_POST['catatan'] ?? '';
$arahan = $_POST['arahan_pilihan'] ?? '';

// Tukar data URL imej kepada binary
$dataURL = $_POST['image'];
$parts = explode(',', $dataURL);
$bin_tandatangan = base64_decode($parts[1]);

// Guna Prepared Statement dengan betul
$sql = "UPDATE minit_surat SET tandatangan_data=?, catatan=?, arahan_pilihan=?, status='SELESAI' WHERE id=?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("error: Database prepare failed: " . $conn->error);
}

// "sssi" bermaksud: 3 string (data ttd, catatan, arahan) dan 1 integer (id)
$stmt->bind_param("sssi", $bin_tandatangan, $catatan, $arahan, $id);

if ($stmt->execute()) {
    $role = $_SESSION['user_role'] ?? '';
    if ($role == 'pengarah') echo "homedirector.php";
    elseif ($role == 'tpa') echo "hometpa.php";
    elseif ($role == 'tpp') echo "hometpp.php";
    else echo "homeadmin.php";
} else {
    echo "error: " . $stmt->error;
}
?>
