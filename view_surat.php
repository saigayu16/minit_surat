<?php
include('db.php');

if (!isset($_GET['id'])) { die("ID tidak dijumpai."); }
$id = intval($_GET['id']);

// Mengambil data surat
$result = $conn->query("SELECT * FROM minit_surat WHERE id = $id");
$row = $result->fetch_assoc();

if (!$row) { die("Data surat tidak ditemui."); }
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Paparan Rasmi - <?= htmlspecialchars($row['no_rujukan']) ?></title>
    <style>
        body { background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('background.jpg'); background-size: cover; background-position: center; background-attachment: fixed; font-family: 'Segoe UI', sans-serif; }
        .wrapper { max-width: 1200px; margin: auto; display: grid; grid-template-columns: 1fr 400px; gap: 20px; padding: 20px; }
        .card { background: rgba(255, 255, 255, 0.95); padding: 25px; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.1); backdrop-filter: blur(5px); }
        .minit-header { border-bottom: 3px solid #2d3748; padding-bottom: 10px; margin-bottom: 20px; }
        .info-row { display: flex; margin-bottom: 10px; }
        .info-label { width: 150px; font-weight: bold; color: #4a5568; }
        .signature-box { margin-top: 30px; padding-top: 20px; border-top: 1px dashed #cbd5e0; }
        .btn-nav { display: inline-block; padding: 8px 15px; background: #4a5568; color: white; text-decoration: none; border-radius: 5px; font-size: 0.9em; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="card">
        <h3>📄 Dokumen Asal</h3>
        <!-- Paparan dari folder uploads/ -->
        <iframe src="uploads/<?= htmlspecialchars($row['fail_surat']) ?>" width="100%" height="600px" style="border:1px solid #ddd;"></iframe>
    </div>

    <div class="card">
        <div class="minit-header"><h2>BORANG MINIT</h2></div>
        
        <div class="info-row"><div class="info-label">Rujukan:</div> <div><?= htmlspecialchars($row['no_rujukan']) ?></div></div>
        <div class="info-row"><div class="info-label">Kolej:</div> <div><?= htmlspecialchars($row['kolej'] ?? '-') ?></div></div>
        <div class="info-row"><div class="info-label">Daripada:</div> <div><?= htmlspecialchars($row['daripada'] ?? '-') ?></div></div>
        <div class="info-row"><div class="info-label">Didaftar:</div> <div><?= $row['created_at'] ?? '-' ?></div></div>
        <hr>
        
        <p><strong>Arahan & Catatan:</strong></p>
        <div style="background: #f0f7ff; padding: 15px; border-left: 5px solid #2563eb; border-radius: 4px;">
            <?php if (!empty($row['arahan_pilihan'])): ?>
                <div style="margin-bottom: 10px; border-bottom: 1px dashed #bfdbfe; padding-bottom: 10px;">
                    <strong>Arahan:</strong> <?= htmlspecialchars($row['arahan_pilihan']) ?>
                </div>
            <?php endif; ?>
            <strong>Catatan:</strong> <?= !empty($row['catatan']) ? nl2br(htmlspecialchars($row['catatan'])) : '<em>Tiada.</em>' ?>
        </div>

        <div class="signature-box">
            <p><strong>Disahkan Oleh:</strong> Pengarah</p>
            <?php if (!empty($row['tandatangan_fail'])): ?>
                <img src="uploads/<?= htmlspecialchars($row['tandatangan_fail']) ?>" width="150px" style="border:1px solid #ccc;">
            <?php else: ?>
                <p style="color:red;"><em>Belum disahkan</em></p>
            <?php endif; ?>
            <p><strong>Tarikh:</strong> <?= !empty($row['status']) && $row['status'] == 'SELESAI' ? date('d/m/Y') : '-' ?></p>
        </div>

        <!-- NAVIGASI KRONOLOGI -->
        <div style="margin-top: 20px; display: flex; justify-content: space-between;">
            <?php
            $prev = $conn->query("SELECT id FROM minit_surat WHERE id < $id ORDER BY id DESC LIMIT 1")->fetch_assoc();
            $next = $conn->query("SELECT id FROM minit_surat WHERE id > $id ORDER BY id ASC LIMIT 1")->fetch_assoc();
            ?>
            <a href="view_surat.php?id=<?= $prev['id'] ?? $id ?>" class="btn-nav" <?= !$prev ? 'style="visibility:hidden"' : '' ?>>⬅ Sebelumnya</a>
            <a href="view_surat.php?id=<?= $next['id'] ?? $id ?>" class="btn-nav" <?= !$next ? 'style="visibility:hidden"' : '' ?>>Seterusnya ➡</a>
        </div>

        <a href="homedirector.php" style="display:block; margin-top:15px; color:#4a5568;">⬅ Kembali ke Dashboard</a>
    </div>
</div>

</body>
</html>
