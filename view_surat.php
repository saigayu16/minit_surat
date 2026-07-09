<?php
include('db.php');

if (!isset($_GET['id'])) { die("ID tidak dijumpai."); }
$id = intval($_GET['id']);

// Mengambil data surat
$result = $conn->query("SELECT * FROM minit_surat WHERE id = $id");
$row = $result->fetch_assoc();

if (!$row) { die("Data surat tidak ditemui."); }

// Tentukan sumber fail untuk dipaparkan
$fail_tempatan = "uploads/" . $row['fail_surat'];
$guna_drive = false;

// Logik paparan: Guna uploads/ jika fail wujud, jika tidak guna Drive
if (!empty($row['fail_surat']) && file_exists($fail_tempatan)) {
    $sumber_fail = $fail_tempatan;
} else if (!empty($row['drive_file_id']) && $row['drive_file_id'] !== "GAGAL_UPLOAD") {
    $sumber_fail = "https://drive.google.com/file/d/" . $row['drive_file_id'] . "/preview";
    $guna_drive = true;
} else {
    $sumber_fail = null;
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <title>Paparan Rasmi - <?= htmlspecialchars($row['no_rujukan']) ?></title>
    <style>
        :root { --primary: #2563eb; --bg: #f1f5f9; --card: #ffffff; --text: #1e293b; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; padding: 20px; color: var(--text); }
        .wrapper { max-width: 1300px; margin: auto; display: grid; grid-template-columns: 1fr 400px; gap: 25px; }
        .card { background: var(--card); padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #0f172a; font-size: 1.25rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        .info-row { display: flex; padding: 8px 0; border-bottom: 1px dashed #e2e8f0; }
        .info-label { width: 140px; font-weight: 600; color: #64748b; }
        .btn-nav { padding: 10px 16px; background: var(--primary); color: white; text-decoration: none; border-radius: 6px; font-weight: 600; transition: 0.3s; }
        .btn-nav:hover { background: #1d4ed8; }
        .btn-back { display: inline-block; margin-top: 20px; color: var(--primary); text-decoration: none; font-weight: 600; }
        iframe { width: 100%; height: 70vh; border-radius: 8px; border: none; background: #f8fafc; }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Bahagian Dokumen -->
    <div class="card">
        <h2>📄 Dokumen: <?= htmlspecialchars($row['no_rujukan']) ?></h2>
        <?php if ($sumber_fail): ?>
            <iframe src="<?= $sumber_fail ?>"></iframe>
        <?php else: ?>
            <div style="height: 600px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border-radius: 8px;">
                <p style="color: #64748b;">Dokumen tidak dijumpai.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bahagian Minit -->
    <div class="card">
        <h2>📝 Borang Minit</h2>
        <div class="info-row"><div class="info-label">Rujukan:</div> <div><?= htmlspecialchars($row['no_rujukan']) ?></div></div>
        <div class="info-row"><div class="info-label">Kolej:</div> <div><?= htmlspecialchars($row['kolej'] ?? '-') ?></div></div>
        <div class="info-row"><div class="info-label">Daripada:</div> <div><?= htmlspecialchars($row['daripada'] ?? '-') ?></div></div>
        
        <div style="margin-top: 20px;">
            <p style="font-weight: 600; color: #64748b;">Catatan:</p>
            <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; min-height: 100px;">
                <?= nl2br(htmlspecialchars($row['catatan'] ?? 'Tiada catatan.')) ?>
            </div>
        </div>
        
        <div style="margin-top: 30px; display: flex; justify-content: space-between;">
            <?php
            $prev = $conn->query("SELECT id FROM minit_surat WHERE id < $id ORDER BY id DESC LIMIT 1")->fetch_assoc();
            $next = $conn->query("SELECT id FROM minit_surat WHERE id > $id ORDER BY id ASC LIMIT 1")->fetch_assoc();
            ?>
            <a href="view_surat.php?id=<?= $prev['id'] ?? $id ?>" class="btn-nav" <?= !$prev ? 'style="visibility:hidden"' : '' ?>>⬅ Sebelumnya</a>
            <a href="view_surat.php?id=<?= $next['id'] ?? $id ?>" class="btn-nav" <?= !$next ? 'style="visibility:hidden"' : '' ?>>Seterusnya ➡</a>
        </div>
        
        <a href="homedirector.php" class="btn-back">⬅ Kembali ke Dashboard</a>
    </div>
</div>

</body>
</html>
