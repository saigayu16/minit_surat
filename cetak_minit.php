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
            background: #cbd5e1;
            font-family: 'Segoe UI', sans-serif; 
        }
        
        .document-container { 
            background: #ffffff; 
            width: 210mm; margin: 0 auto; padding: 20mm; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            box-sizing: border-box;
        }
        
        .header-title { font-size: 22px; font-weight: 800; color: #1e293b; border-bottom: 3px solid #1e293b; padding-bottom: 8px; margin-bottom: 15px; text-transform: uppercase; }
        
        .sticky-note { 
            background: #fffbeb; padding: 20px; border-radius: 4px; border-left: 8px solid #f59e0b; 
            box-shadow: 2px 2px 10px rgba(0,0,0,0.05); margin: 20px 0; position: relative;
        }
        .arahan-badge { background: #f59e0b; color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; margin-bottom: 8px; display: inline-block; }

        .stamp-box { 
            border: 2px solid #1e293b; padding: 10px; width: 200px; text-align: center; 
            float: right; margin: 20px 0; background: #fff; position: relative;
        }
        .sig-image { max-height: 50px; display: block; margin: 0 auto 5px auto; }

        .section-divider {
            margin: 40px 0 20px 0;
            border-top: 2px dashed #94a3b8;
            padding-top: 20px;
        }

        .original-doc-container {
            width: 100%;
            text-align: center;
        }
        
        /* Tetapan iframe tanpa border luar yang mengganggu */
        .original-doc-container iframe {
            width: 100%;
            height: 1400px; 
            border: none;
            background: #fff;
            overflow: hidden;
        }

        .btn-container { position: fixed; bottom: 30px; right: 30px; display: flex; gap: 10px; z-index: 999; }
        .btn-action { padding: 15px 30px; border-radius: 50px; border: none; cursor: pointer; font-weight: 600; box-shadow: 0 4px 10px rgba(0,0,0,0.3); transition: 0.3s; text-decoration: none; display: inline-block; }
        .btn-print { background: #0f172a; color: white; }
        .btn-back { background: #e2e8f0; color: #475569; }
        .btn-action:hover { transform: scale(1.05); }

        @media print { 
            .no-print { display: none !important; } 
            body { background: white; padding: 0; } 
            .document-container { box-shadow: none; width: 100%; padding: 10mm; margin: 0; } 
            .original-doc-container iframe { height: 1300px !important; border: none; }
        }
    </style>
</head>
<body>

<div class="document-container">
    
    <!-- BAHAGIAN 1: BORANG MINIT CERAIAN -->
    <div class="header-title">Borang Minit Ceraian</div>
    
    <table width="100%" cellpadding="8" border="0" style="border-collapse: collapse; margin-bottom: 15px; font-size: 14px;">
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
        <div style="font-size: 15px; color: #451a03; line-height: 1.5;"><?= $catatan ?></div>
    </div>

    <?php if (!empty($signature_data)) { ?>
        <div style="clear: both;"></div>
        <div class="stamp-box">
            <img src="<?= $signature_data ?>" class="sig-image">
            <div style="border-top: 1px solid #000; font-size: 10px; font-weight: bold; padding-top: 4px;">
                PENGARAH<br><?= $tarikh_sah ?>
            </div>
        </div>
        <div style="clear: both;"></div>
    <?php } ?>

    <!-- BAHAGIAN 2: DOKUMEN / BORANG ASAL GOOGLE DRIVE -->
    <div class="section-divider"></div>
    
    <div class="header-title">Dokumen / Borang Asal Lampiran</div>
    <div class="original-doc-container">
        <?php if (!empty($drive_file_id)) { 
            if (filter_var($drive_file_id, FILTER_VALIDATE_URL) || strpos($drive_file_id, 'drive.google.com') !== false) {
                $embed_url = str_replace('/view?usp=sharing', '/preview', $drive_file_id);
                $embed_url = str_replace('/view?usp=drivesdk', '/preview', $embed_url);
                if(strpos($embed_url, '/preview') === false && strpos($embed_url, 'id=') !== false) {
                    $embed_url = str_replace('view?', 'preview?', $embed_url);
                }
            } else {
                $embed_url = "https://drive.google.com/file/d/" . htmlspecialchars($drive_file_id) . "/preview";
            }
        ?>
            <!-- Menggunakan parameter em=true dan rm=minimal untuk menyembunyikan kawalan bar Google Drive -->
            <iframe src="<?= htmlspecialchars($embed_url) ?>?rm=minimal&em=true" scrolling="no" allow="autoplay"></iframe>
        <?php } else { ?>
            <p style="color: #ef4444; font-weight: bold; margin-top: 20px;">
                <i class="fa-solid fa-triangle-exclamation"></i> Tiada ID Google Drive dijumpai dalam kolum `drive_file_id` untuk rekod ini.
            </p>
        <?php } ?>
    </div>

</div>

<div class="btn-container no-print">
    <a href="homeadmin.php" class="btn-action btn-back">
        <i class="fa-solid fa-arrow-left"></i> KEMBALI
    </a>
    <button class="btn-action btn-print" onclick="window.print()">
        <i class="fa-solid fa-print"></i> CETAK / SIMPAN SEMUA SEKALIGUS
    </button>
</div>

</body>
</html>
<?php ob_end_flush(); ?>
