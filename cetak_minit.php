<?php 
ob_start();
session_start();
include('db.php');

if (!isset($_GET['id']) || empty($_GET['id'])) { die("ID Dokumen tidak sah."); }

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM minit_surat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) { die("Rekod tidak ditemui."); }

$status = strtoupper(trim($row['status'] ?? 'TIADA STATUS'));
$no_rujukan = htmlspecialchars($row['no_rujukan'] ?? '-');
$tarikh_terima = !empty($row['tarikh_terima']) ? date('d/m/Y', strtotime($row['tarikh_terima'])) : '-';
$daripada = htmlspecialchars($row['daripada'] ?? '-');
$didaftarkan_oleh = htmlspecialchars($row['didaftarkan_oleh'] ?? 'Admin');
$catatan = !empty($row['catatan']) ? $row['catatan'] : 'Tiada catatan tambahan diberikan.';
$arahan = htmlspecialchars($row['arahan_pilihan'] ?? 'Tiada arahan.');
$tarikh_sah = !empty($row['tarikh_sah']) ? date('d/m/Y', strtotime($row['tarikh_sah'])) : date('d/m/Y');
$signature_data = $row['tandatangan']; 
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Cetak Minit - <?= $no_rujukan ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; padding: 20px; background-color: #e2e8f0; font-family: 'Segoe UI', sans-serif; }
        .page-box { background: #fff; width: 210mm; margin: 0 auto 30px auto; padding: 60px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px rgba(0,0,0,0.1); box-sizing: border-box; }
        .document-view-box { width: 210mm; height: 800px; margin: 0 auto 30px auto; border: 2px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #fff; }
        .header-modern { border-bottom: 2px solid #0f172a; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #f8fafc; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; }
        .sticky-note { background: #fff; border: 2px dashed #cbd5e1; padding: 25px; margin: 30px 0; position: relative; }
        .footer-signature { display: flex; justify-content: flex-end; margin-top: 40px; }
        .sig-box { border: 1px solid #e2e8f0; padding: 10px; width: 200px; text-align: center; background: #f8fafc; }
        .btn-print { background: #2563eb; color: white; padding: 15px 40px; border: none; border-radius: 8px; cursor: pointer; position: fixed; bottom: 30px; right: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        @media print { .no-print { display: none !important; } body { background: #fff; } .page-box { margin: 0 auto; box-shadow: none; border: none; padding: 20mm; } }
    </style>
</head>
<body>

<div class="document-view-box no-print">
    <div style="padding: 10px; background: #f1f5f9; font-weight: bold; border-bottom: 1px solid #ddd;">
        <i class="fa-solid fa-file-pdf"></i> DOKUMEN ASAL
    </div>
    <iframe src="papar_dokumen.php?id=<?= $id ?>" width="100%" height="100%" frameborder="0"></iframe>
</div>

<div class="page-box">
    <div class="header-modern">
        <h1>MINIT CERAIAN</h1>
        <div style="color: #10b981; font-weight: bold;"><i class="fa-solid fa-check-circle"></i> <?= $status ?></div>
    </div>
    <div class="info-grid">
        <div><div style="font-size:10px; font-weight:bold;">NO. RUJUKAN</div><strong><?= $no_rujukan ?></strong></div>
        <div><div style="font-size:10px; font-weight:bold;">TARIKH TERIMA</div><strong><?= $tarikh_terima ?></strong></div>
        <div><div style="font-size:10px; font-weight:bold;">DARIPADA</div><strong><?= $daripada ?></strong></div>
        <div><div style="font-size:10px; font-weight:bold;">DIDAFTARKAN OLEH</div><strong><?= $didaftarkan_oleh ?></strong></div>
    </div>
    <div class="sticky-note">
        <div style="color: #2563eb; font-weight: bold; margin-bottom: 10px;">ARAHAN: <?= $arahan ?></div>
        <div style="line-height: 1.6;"><?= nl2br(htmlspecialchars($catatan)) ?></div>
    </div>
    <div class="footer-signature">
        <?php if (!empty($signature_data)): ?>
            <div class="sig-box">
                <img src="<?= $signature_data ?>" style="max-height: 50px; width: auto;">
                <div style="font-size: 10px; margin-top: 5px; border-top: 1px solid #ccc; pt: 5px;">
                    <b>PENGARAH</b><br><?= $tarikh_sah ?>
                </div>
            </div>
        <?php else: ?>
            <div style="color: #94a3b8; font-style: italic;">(Belum disahkan oleh Pengarah)</div>
        <?php endif; ?>
    </div>
</div>

<button class="btn-print no-print" onclick="window.print()">
    <i class="fa-solid fa-print"></i> CETAK BORANG
</button>
</body>
</html>
<?php ob_end_flush(); ?>
