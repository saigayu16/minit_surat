<?php
include('db.php');

if (!isset($_GET['id'])) { die("ID tidak dijumpai."); }
$id = intval($_GET['id']);

// Mengambil data surat (Sila pastikan kolum 'tandatangan' wujud dalam jadual anda)
$result = $conn->query("SELECT * FROM minit_surat WHERE id = $id");
$row = $result->fetch_assoc();

if (!$row) { die("Data surat tidak ditemui."); }

// Tentukan sumber fail
$fail_tempatan = "uploads/" . $row['fail_surat'];
if (!empty($row['fail_surat']) && file_exists($fail_tempatan)) {
    $sumber_fail = $fail_tempatan;
} else if (!empty($row['drive_file_id']) && $row['drive_file_id'] !== "GAGAL_UPLOAD") {
    $sumber_fail = "https://drive.google.com/file/d/" . $row['drive_file_id'] . "/preview";
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
        :root { --primary: #2563eb; --bg: #f1f5f9; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); padding: 20px; }
        .wrapper { max-width: 1300px; margin: auto; display: grid; grid-template-columns: 1fr 400px; gap: 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; }
        .signature-box { margin-top: 20px; padding: 15px; border: 1px dashed #cbd5e1; border-radius: 8px; background: #f8fafc; }
        .signature-img { max-width: 200px; height: auto; display: block; margin-top: 10px; }
        iframe { width: 100%; height: 60vh; border: none; }
        .btn-nav { padding: 10px 16px; background: #4a5568; color: white; text-decoration: none; border-radius: 6px; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="card">
        <h3>📄 Dokumen: <?= htmlspecialchars($row['no_rujukan']) ?></h3>
        <?php if ($sumber_fail): ?>
            <iframe src="<?= $sumber_fail ?>"></iframe>
        <?php else: ?>
            <div style="height: 400px; display: flex; align-items: center; justify-content: center; background: #eee;">Dokumen tidak dijumpai.</div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>BORANG MINIT</h2>
        <p><strong>Catatan:</strong> <?= nl2br(htmlspecialchars($row['catatan'] ?? 'Tiada')) ?></p>
        
        <!-- Bahagian Tandatangan -->
        <div class="signature-box">
            <strong>Tandatangan Pengarah:</strong>
            <?php if (!empty($row['tandatangan'])): ?>
                <img src="<?= $row['tandatangan'] ?>" alt="Tandatangan" class="signature-img">
            <?php else: ?>
                <p style="color: #94a3b8; font-style: italic; margin-top: 10px;">Belum ditandatangani.</p>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px; display: flex; justify-content: space-between;">
            <a href="homedirector.php" style="color:var(--primary); text-decoration:none;">⬅ Kembali ke Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>
