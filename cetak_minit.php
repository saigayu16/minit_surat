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

// Data Formatting
$status = strtoupper(trim($row['status'] ?? 'TIADA STATUS'));
$no_rujukan = htmlspecialchars($row['no_rujukan'] ?? '-');
$tarikh_terima = !empty($row['tarikh_terima']) ? date('d/m/Y', strtotime($row['tarikh_terima'])) : '-';
$daripada = htmlspecialchars($row['daripada'] ?? '-');
$didaftarkan_oleh = htmlspecialchars($row['didaftarkan_oleh'] ?? 'Admin');
$catatan = !empty($row['catatan']) ? nl2br(htmlspecialchars($row['catatan'])) : '<em>Tiada catatan diberikan.</em>';
$arahan = htmlspecialchars($row['arahan_pilihan'] ?? 'TIADA ARAHAN');
$tarikh_sah = !empty($row['tarikh_sah']) ? date('d/m/Y', strtotime($row['tarikh_sah'])) : date('d/m/Y');
$signature_data = $row['tandatangan']; 
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Borang Minit - <?= $no_rujukan ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; padding: 40px 20px; background-color: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .page-box { 
            background: #fff; width: 210mm; margin: 0 auto; padding: 25mm; 
            border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            box-sizing: border-box; min-height: 297mm; position: relative;
        }
        .header-section { border-bottom: 3px solid #1e293b; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .header-section h1 { margin: 0; font-size: 28px; color: #1e293b; letter-spacing: 1px; }
        .status-badge { background: #dcfce7; color: #166534; padding: 6px 15px; border-radius: 50px; font-size: 12px; font-weight: 800; text-transform: uppercase; }
        
        .grid-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-box { border: 1px solid #cbd5e1; padding: 15px; border-radius: 6px; background: #f8fafc; }
        .label { font-size: 11px; color: #64748b; font-weight: 700; margin-bottom: 5px; text-transform: uppercase; }
        .value { font-size: 14px; color: #0f172a; font-weight: 600; }

        .minit-container { border: 2px solid #334155; padding: 25px; border-radius: 8px; margin-top: 20px; }
        .arahan-title { font-size: 13px; color: #2563eb; font-weight: 800; margin-bottom: 15px; text-transform: uppercase; border-bottom: 1px solid #dbeafe; padding-bottom: 10px; }
        
        .footer-area { position: absolute; bottom: 80px; right: 80px; text-align: center; }
        .sig-image { max-height: 70px; margin-bottom: 10px; }
        .sig-line { border-top: 2px solid #0f172a; width: 200px; padding-top: 5px; font-size: 12px; font-weight: bold; }

        .btn-print { background: #1e293b; color: white; padding: 15px 30px; border: none; border-radius: 50px; cursor: pointer; position: fixed; bottom: 30px; right: 30px; font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.3); transition: 0.3s; }
        .btn-print:hover { background: #0f172a; transform: scale(1.05); }

        @media print { .no-print { display: none !important; } body { background: #fff; padding: 0; } .page-box { box-shadow: none; border: none; } }
    </style>
</head>
<body>

<div class="page-box">
    <div class="header-section">
        <h1>BORANG MINIT RASMI</h1>
        <div class="status-badge"><i class="fa-solid fa-circle-check"></i> <?= $status ?></div>
    </div>

    <div class="grid-container">
        <div class="info-box"><div class="label">No. Rujukan</div><div class="value"><?= $no_rujukan ?></div></div>
        <div class="info-box"><div class="label">Tarikh Terima</div><div class="value"><?= $tarikh_terima ?></div></div>
        <div class="info-box"><div class="label">Daripada</div><div class="value"><?= $daripada ?></div></div>
        <div class="info-box"><div class="label">Didaftarkan Oleh</div><div class="value"><?= $didaftarkan_oleh ?></div></div>
    </div>

    <div class="minit-container">
        <div class="arahan-title"><i class="fa-solid fa-thumbtack"></i> Arahan: <?= $arahan ?></div>
        <div style="font-size: 15px; color: #334155; line-height: 1.8;"><?= $catatan ?></div>
    </div>

    <div class="footer-area">
        <?php if (!empty($signature_data)): ?>
            <img src="<?= $signature_data ?>" class="sig-image">
            <div class="sig-line">
                PENGARAH<br><?= $tarikh_sah ?>
            </div>
        <?php else: ?>
            <div style="color: #94a3b8; font-style: italic; border: 1px dashed #ccc; padding: 20px;">Menunggu Tandatangan</div>
        <?php endif; ?>
    </div>
</div>

<button class="btn-print no-print" onclick="window.print()">
    <i class="fa-solid fa-print"></i> CETAK BORANG INI
</button>

</body>
</html>
<?php ob_end_flush(); ?>
