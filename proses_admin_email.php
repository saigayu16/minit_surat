<?php
include('db.php');
include('mailer.php');

$staf_id = $_POST['staf_id'];

$stmt = $conn->prepare("SELECT email, nama FROM staff WHERE id = ?");
$stmt->bind_param("i", $staf_id);
$stmt->execute();
$staf = $stmt->get_result()->fetch_assoc();

if ($staf) {
    $subjek = "Notifikasi Minit Surat Daripada Admin";
    $kandungan = "Hai " . $staf['nama'] . ", sila semak sistem untuk tindakan surat baharu.";

    if (hantarEmail($staf['email'], $staf['nama'], $subjek, $kandungan)) {
        echo "<script>alert('Berjaya! Emel telah dihantar.'); window.location='list_staf.php';</script>";
    } else {
        echo "<script>alert('Ralat: Gagal menghantar emel.'); window.history.back();</script>";
    }
}
?>
