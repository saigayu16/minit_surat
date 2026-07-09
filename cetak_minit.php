<?php
session_start();
include('db.php');

if (!isset($_GET['id']) || empty($_GET['id'])) { die("ID Dokumen tidak sah."); }

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM minit_surat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) { die("Rekod tidak ditemui."); }

// Data
$status = strtoupper(trim($row['status'] ?? 'TIADA STATUS'));
$no_rujukan = htmlspecialchars($row['no_rujukan'] ?? '-');
$tarikh_terima = !empty($row['tarikh_terima']) ? date('d/m/Y', strtotime($row['tarikh_terima'])) : '-';
$daripada = htmlspecialchars($row['daripada'] ?? '-');
$didaftarkan_oleh = htmlspecialchars($row['didaftarkan_oleh'] ?? 'Admin');
$perkara = htmlspecialchars($row['perkara'] ?? '-');
$catatan = $row['catatan'] ?? 'Tiada catatan.';
$arahan = $row['arahan_pilihan'] ?? 'Tiada arahan';
$tarikh_sah = !empty($row['tarikh_sah']) ? date('d/m/Y', strtotime($row['tarikh_sah'])) : date('d/m/Y');

// LOGIK TANDATANGAN:
// Jika kolum 'tandatangan' anda menyimpan nama fail (cth: signature.png)
// Kita sambungkan dengan folder 'uploads/'
$sig_file = $row['tandatangan']; 
$signature_src = (!empty($sig_file)) ? 'uploads/' . $sig_file : "";

// Fail Dokumen (Gunakan papar_fail.php untuk elak Forbidden)
$url_dokumen = "papar_fail.php?id=" . $id; 
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Cetak Minit - <?= $no_rujukan ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; padding: 20px; background-color: #e2e8f0; font-family: 'Segoe UI', sans-serif; }
        .page-box { background: #fff; width: 210mm; margin: 0 auto 30px auto; padding: 60px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .document-view-box { width: 210mm; height: 600px; margin: 0 auto 30px auto; border: 2px solid #cbd5e1; background: #fff; }
        .footer-signature { display: flex; justify-content: flex-end; margin-top: 40px; }
        .sig-box { border: 1px solid #000; padding: 10px; width: 200px; text-align: center; }
        .sig-box img { max-width: 150px; height: auto; }
        @media print { .no-print { display: none !important; } body { background: #fff; } .page-box { box-shadow: none; border: none; } }
    </style>
</head>
<body>

<!-- Bahagian Dokumen -->
<div class="document-view-box no-print">
    <iframe src="<?= $url_dokumen ?>" width="100%" height="100%" frameborder="0"></iframe>
</div>

<!-- Bahagian Borang -->
<div class="page-box">
    <h1>MINIT CERAIAN: <?= $no_rujukan ?></h1>
    <p>Status: <strong><?= $status ?></strong></p>
    
    <div class="sticky-note">
        <p><strong>ARAHAN:</strong> <?= htmlspecialchars($arahan) ?></p>
        <p><?= nl2br(htmlspecialchars($catatan)) ?></p>
    </div>

    <div class="footer-signature">
        <?php if (!empty($signature_src) && file_exists($signature_src)): ?>
            <div class="sig-box">
                <img src="<?= $signature_src ?>" alt="Tandatangan">
                <div style="border-top: 1px solid #000; margin-top: 5px;">PENGARAH<br><?= $tarikh_sah ?></div>
            </div>
        <?php else: ?>
            <div style="color: red;">(Tandatangan belum dimuat naik)</div>
        <?php endif; ?>
    </div>
</div>

<button class="no-print" onclick="window.print()">CETAK</button>

</body>
</html>
