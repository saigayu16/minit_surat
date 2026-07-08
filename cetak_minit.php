<?php
session_start();
include('db.php');

// 1. Semak ID
if (!isset($_GET['id']) || empty($_GET['id'])) { die("ID Dokumen tidak sah."); }

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM minit_surat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) { die("Rekod tidak ditemui."); }

// 2. Variabel
$status = strtoupper(trim($row['status'] ?? 'TIADA STATUS'));
$no_rujukan = htmlspecialchars($row['no_rujukan'] ?? '-');
$tarikh_terima = !empty($row['tarikh_terima']) ? date('d/m/Y', strtotime($row['tarikh_terima'])) : '-';
$daripada = htmlspecialchars($row['daripada'] ?? '-');
$didaftarkan_oleh = htmlspecialchars($row['didaftarkan_oleh'] ?? 'Admin');
$perkara = htmlspecialchars($row['perkara'] ?? '-');

// Mengambil catatan daripada database (Jika kosong, tunjukkan mesej lalai)
$catatan = !empty($row['catatan']) ? $row['catatan'] : 'Tiada catatan tambahan diberikan.';

$nama_fail = htmlspecialchars($row['fail_surat'] ?? 'Dokumen Asal');
$tarikh_sah = !empty($row['tarikh_sah']) ? date('d/m/Y', strtotime($row['tarikh_sah'])) : date('d/m/Y');
$signature_img = !empty($row['tandatangan_fail']) ? 'uploads/' . $row['tandatangan_fail'] : "";
$fail_path = 'uploads/' . htmlspecialchars($row['fail_surat'] ?? '');
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Borang Minit - <?= $no_rujukan ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
    margin: 0; 
    padding: 20px; 
    /* Warna latar belakang asal */
    background-color: #e2e8f0; 
    /* Tetapan Imej Latar Belakang */
    background-image: url('daftarsurat.jpg'); 
    background-size: cover;          /* Memastikan imej memenuhi skrin */
    background-position: center;     /* Meletakkan imej di tengah */
    background-attachment: fixed;    /* Imej kekal statik apabila skrol */
    background-repeat: no-repeat;
    font-family: 'Segoe UI', sans-serif; 
}
        
        .page-box { 
            background: #fff; width: 210mm; margin: 0 auto 30px auto; padding: 60px; 
            border: 1px solid #cbd5e1; box-shadow: 0 4px 6px rgba(0,0,0,0.1); box-sizing: border-box; 
        }
        
        .document-view-box { height: 800px; margin-bottom: 30px; border: 2px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #fff; }
        
        .header-modern { border-bottom: 2px solid #0f172a; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; }
        .header-modern h1 { margin: 0; font-size: 24px; color: #0f172a; text-transform: uppercase; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #f8fafc; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; }
        .info-label { font-size: 10px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; }
        
        /* Gaya Catatan */
        .sticky-note { background: #fff; border: 2px dashed #cbd5e1; padding: 25px; margin: 30px 0; position: relative; min-height: 100px; }
        .sticky-note::before { content: "CATATAN / MINIT ARAHAN"; position: absolute; top: -12px; left: 20px; background: #fff; padding: 0 10px; font-size: 11px; font-weight: bold; color: #2563eb; }
        .catatan-content { color: #334155; line-height: 1.6; font-size: 15px; }

        .footer-signature { display: flex; justify-content: flex-end; margin-top: 60px; }
        .sig-box { border: 1px solid #e2e8f0; padding: 15px; width: 200px; text-align: center; border-radius: 4px; background: #f8fafc; }
        
        .btn-print { background: #2563eb; color: white; padding: 15px 40px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 14px; position: fixed; bottom: 30px; right: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .btn-print:hover { background: #1d4ed8; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff; padding: 0; }
            .page-box { box-shadow: none; border: none; margin: 0 auto; padding: 20mm; }
        }
    </style>
</head>
<body>

<div class="document-view-box no-print">
    <div style="padding: 10px; background: #f1f5f9; border-bottom: 1px solid #ddd; font-size: 12px; font-weight: bold;">
        <i class="fa-solid fa-file-pdf"></i> DOKUMEN ASAL: <?= $nama_fail ?>
    </div>
    <iframe src="<?= $fail_path ?>" width="100%" height="100%" frameborder="0"></iframe>
</div>

<div class="page-box">
    <div class="header-modern">
        <h1>MINIT CERAIAN</h1>
        <div style="color: #10b981; font-weight: bold;"><i class="fa-solid fa-check-circle"></i> <?= $status ?></div>
    </div>

    <div class="info-grid">
        <div><div class="info-label">No. Rujukan</div><strong><?= $no_rujukan ?></strong></div>
        <div><div class="info-label">Tarikh Terima</div><strong><?= $tarikh_terima ?></strong></div>
        <div><div class="info-label">Daripada</div><strong><?= $daripada ?></strong></div>
        <div><div class="info-label">Didaftarkan Oleh</div><strong><?= $didaftarkan_oleh ?></strong></div>
    </div>

    <div style="margin-top: 30px;">
        <div class="info-label">Perkara / Tajuk</div>
        <div style="font-weight: 600; font-size: 16px; color: #1e293b; padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
            <?= $perkara ?>
        </div>
    </div>

    <div class="sticky-note">
    <div style="color: #2563eb; font-weight: bold; margin-bottom: 5px;">
        ARAHAN: <?= htmlspecialchars($row['arahan_pilihan']) ?>
    </div>
    <div class="catatan-content">
        <?= nl2br(htmlspecialchars($row['catatan'])) ?>
    </div>
</div>

    <div class="footer-signature">
    <?php if (strcasecmp($status, 'SELESAI') == 0 && !empty($row['tandatangan_data'])): ?>
        <div class="sig-box">
            <img src="data:image/png;base64,<?= base64_encode($row['tandatangan_data']) ?>" style="max-height: 45px; width: auto;">
            <div style="font-size: 10px; margin-top: 10px; border-top: 1px solid #cbd5e1; padding-top: 5px;">
                <b>PENGARAH</b><br><?= $tarikh_sah ?>
            </div>
        </div>
    <?php else: ?>
        <div style="color: #94a3b8; font-style: italic;">(Menunggu tandatangan pengarah)</div>
    <?php endif; ?>
</div>
</div>

<button class="btn-print no-print" onclick="window.print()">
    <i class="fa-solid fa-print"></i> CETAK BORANG SAHAJA
</button>

</body>
</html>
