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

// Data Formatting Minit
$status = strtoupper(trim($row['status'] ?? 'TIADA STATUS'));
$no_rujukan = htmlspecialchars($row['no_rujukan'] ?? '-');
$tarikh_terima = !empty($row['tarikh_terima']) ? date('d/m/Y', strtotime($row['tarikh_terima'])) : '-';
$daripada = htmlspecialchars($row['daripada'] ?? '-');
$didaftarkan_oleh = htmlspecialchars($row['didaftarkan_oleh'] ?? 'Admin');
$catatan = !empty($row['catatan']) ? nl2br(htmlspecialchars($row['catatan'])) : '<em>Tiada catatan diberikan.</em>';
$arahan = htmlspecialchars($row['arahan_pilihan'] ?? 'TIADA ARAHAN');
$tarikh_sah = !empty($row['tarikh_sah']) ? date('d/m/Y', strtotime($row['tarikh_sah'])) : date('d/m/Y');
$signature_data = $row['tandatangan']; 

// BACA ID DARI DRIVE MENGIKUT KOLUM SEBENAR: drive_file_id
$drive_file_id = trim($row['drive_file_id'] ?? ''); 
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Cetak Dokumen & Minit - <?= $no_rujukan ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            margin: 0; padding: 20px; 
            background-image: url('daftarsurat.jpg');
            background-size: cover; 
            background-position: center; 
            background-attachment: fixed; 
            background-repeat: no-repeat;
            font-family: 'Segoe UI', sans-serif; 
        }
        
        .page-box { 
            background: rgba(255, 255, 255, 0.95); 
            width: 210mm; margin: 0 auto 50px auto; padding: 25mm; 
            border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            min-height: 297mm; position: relative; box-sizing: border-box;
            page-break-after: always; 
        }
        
        .header-title { font-size: 24px; font-weight: 800; color: #1e293b; border-bottom: 3px solid #1e293b; padding-bottom: 10px; margin-bottom: 20px; text-transform: uppercase; }
        
        .sticky-note { 
            background: #fffbeb; padding: 25px; border-radius: 4px; border-left: 10px solid #f59e0b; 
            box-shadow: 5px 5px 15px rgba(0,0,0,0.1); margin: 30px 0; position: relative;
        }
        .sticky-note::after { content: "PENTING"; position: absolute; top: 10px; right: 10px; font-size: 10px; color: #b45309; font-weight: bold; }
        .arahan-badge { background: #f59e0b; color: #fff; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; margin-bottom: 10px; display: inline-block; }

        .stamp-box { 
            border: 3px solid #1e293b; padding: 15px; width: 220px; text-align: center; 
            float: right; margin-top: 40px; background: #fff; position: relative;
        }
        .stamp-box::before { content: "TANDATANGAN RASMI"; position: absolute; top: -12px; background: white; padding: 0 5px; font-size: 9px; font-weight: bold; color: #1e293b; }
        .sig-image { max-height: 60px; display: block; margin: 0 auto 5px auto; }

        .original-doc-container {
            width: 100%;
            text-align: center;
        }
        .original-doc-container iframe {
            width: 100%;
            height: 850px;
            border: 1px solid #cbd5e1;
        }

        .btn-container { position: fixed; bottom: 30px; right: 30px; display: flex; gap: 10px; z-index: 999; }
        .btn-action { padding: 15px 30px; border-radius: 50px; border: none; cursor: pointer; font-weight: 600; box-shadow: 0 4px 10px rgba(0,0,0,0.3); transition: 0.3s; text-decoration: none; display: inline-block; }
        .btn-print { background: #0f172a; color: white; }
        .btn-back { background: #e2e8f0; color: #475569; }
        .btn-action:hover { transform: scale(1.05); }

        @media print { 
            .no-print { display: none !important; } 
            body { background: white; padding: 0; } 
            .page-box { box-shadow: none; border: none; margin: 0 auto; page-break-after: always; } 
        }
    </style>
</head>
<body>

<!-- MUKA SURAT 1: BORANG MINIT CERAIAN -->
<div class="page-box">
    <div class="header-title">Borang Minit Ceraian</div>
    
    <table width="100%" cellpadding="10" border="0" style="border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td width="50%" style="border: 1px solid #e2e8f0;"><strong>No. Rujukan:</strong><br><?= $no_rujukan ?></td>
            <td width="50%" style="border: 1px solid #e2e8f0;"><strong>Tarikh Terima:</strong><br><?= $tarikh_terima ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0;"><strong>Daripada:</strong><br><?= $daripada ?></td>
            <td style="border: 1px solid #e2e8f0;"><strong>Didaftarkan Oleh:</strong><br><?= $didaftarkan_oleh ?></td>
        </tr>
    </table>

    <div class="sticky-note">
        <div class="arahan-badge"><i class="fa-solid fa-bolt"></i> ARAHAN: <?= $arahan ?></div>
        <div style="font-size: 16px; color: #451a03; line-height: 1.6;"><?= $catatan ?></div>
    </div>

    <?php if (!empty($signature_data)): ?>
        <div class="stamp-box">
            <img src="<?= $signature_data ?>" class="sig-image">
            <div style="border-top: 1px solid #000; font-size: 11px; font-weight: bold; padding-top: 5px;">
                PENGARAH<br><?= $tarikh_sah ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- MUKA SURAT 2: DOKUMEN ASAL DARI GOOGLE DRIVE -->
<div class="page-box">
    <div class="header-title">Dokumen / Borang Asal Lampiran</div>
    <div class="original-doc-container">
        <?php if (!empty($drive_file_id)): ?>
            <?php 
                // Jika nilai dalam 'drive_file_id' adalah pautan penuh URL Google Drive
                if (filter_var($drive_file_id, FILTER_VALIDATE_URL) || strpos($drive_file_id, 'drive.google.com') !== false): 
                    $embed_url = str_replace('/view?usp=sharing', '/preview', $drive_file_id);
                    $embed_url = str_replace('/view?usp=drivesdk', '/preview', $embed_url);
                    if(strpos($embed_url, '/preview') === false && strpos($embed_url, 'id=') !== false) {
                        $embed_url = str_replace('view?', 'preview?', $embed_url);
                    }
            ?>
                <iframe src="<?= htmlspecialchars($embed_url) ?>" allow="autoplay"></iframe>

            <?php else: ?>
                <!-- Jika nilai dalam 'drive_file_id' ialah ID fail sahaja (cth: 1B2v... ) -->
                <iframe src="https://drive.google.com/file/d/<?= htmlspecialchars($drive_file_id) ?>/preview" allow="autoplay"></iframe>
            <?php endif; ?>

        <?php else: ?>
            <p style="color: #ef4444; font-weight: bold; margin-top: 50px;">
                <i class="fa-solid fa-triangle-exclamation"></i> Tiada ID Google Drive dijumpai dalam kolum `drive_file_id` untuk rekod ini.
            </p>
        <?php endif; ?>
    </div>
</div>

<div class="btn-container no-print">
    <a href="homeadmin.php" class="btn-action btn-back">
        <i class="fa-solid fa-arrow-left"></i> KEMBALI
    </a>
    <button class="btn-action btn-print" onclick="window.print()">
        <i class="fa-solid fa-print"></i> CETAK KEDUA-DUA BORANG
    </button>
</div>

</body>
</html>
<?php ob_end_flush(); ?>
