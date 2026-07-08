<?php
include('db.php');
include('mailer.php'); // Panggil fail fungsi mailer.php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_staf = $_POST['nama_staf'];
    $email_staf = $_POST['email'];
    $surat_id = $_POST['surat_id'];

    // 1. Proses Fail (Jika ada dokumen_minit)
    $base64_file = null;
    $file_name = null;

    if (isset($_FILES['dokumen_minit']) && $_FILES['dokumen_minit']['error'] == 0) {
        $file_name = $_FILES['dokumen_minit']['name'];
        $file_content = file_get_contents($_FILES['dokumen_minit']['tmp_name']);
        $base64_file = base64_encode($file_content);
    } else {
        echo "<script>alert('Sila lampirkan fail minit.'); window.history.back();</script>";
        exit;
    }

    // 2. Panggil fungsi hantarEmail
    $subjek = "Notifikasi Minit Surat";
    $kandungan = "Hai <strong>$nama_staf</strong>,<br><br>Sila semak lampiran minit surat anda.";

    if (hantarEmail($email_staf, $nama_staf, $subjek, $kandungan, $base64_file, $file_name)) {
        echo "<script>alert('Berjaya! Emel telah dihantar kepada $nama_staf'); window.location='homeadmin.php';</script>";
    } else {
        echo "<script>alert('Ralat: Gagal menghantar emel. Sila semak API Key.'); window.history.back();</script>";
    }
}
?>
