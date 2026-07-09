<?php
include('db.php');
if (!isset($_GET['id'])) { die("ID tidak dijumpai."); }
$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM minit_surat WHERE id = $id");
$row = $result->fetch_assoc();
if (!$row) { die("Data surat tidak ditemui."); }

// Logik Sumber Fail
$fail_tempatan = "uploads/" . $row['fail_surat'];
$sumber_fail = (file_exists($fail_tempatan)) ? $fail_tempatan : "https://drive.google.com/file/d/" . $row['drive_file_id'] . "/preview";
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; --card: #ffffff; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; padding: 20px; color: #1e293b; }
        .container { max-width: 1300px; margin: auto; display: grid; grid-template-columns: 1fr 400px; gap: 25px; }
        .card { background: var(--card); padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        .meta-info { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
        .meta-item { display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding: 8px 0; }
        iframe { width: 100%; height: 75vh; border-radius: 8px; border: none; background: #f1f5f9; }
        .btn-back { display: inline-block; padding: 10px 20px; background: var(--primary); color: white; text-decoration: none; border-radius: 6px; font-weight: 600; transition: 0.3s; }
        .btn-back:hover { background: #1d4ed8; }
    </style>
</head>
<body>

<div class="container">
    <!-- Bahagian Dokumen -->
    <div class="card">
        <h2>📄 Dokumen Rujukan: <?= htmlspecialchars($row['no_rujukan']) ?></h2>
        <iframe src="<?= $sumber_fail ?>"></iframe>
    </div>

    <!-- Bahagian Minit -->
    <div class="card">
        <h2>📝 Butiran Minit</h2>
        <div class="meta-info">
            <div class="meta-item"><span>Rujukan:</span> <b><?= htmlspecialchars($row['no_rujukan']) ?></b></div>
            <div class="meta-item"><span>Tarikh Terima:</span> <b><?= htmlspecialchars($row['tarikh_terima']) ?></b></div>
            <div class="meta-item"><span>Daripada:</span> <b><?= htmlspecialchars($row['daripada']) ?></b></div>
            <div class="meta-item"><span>Status:</span> <b style="color:var(--primary)"><?= htmlspecialchars($row['status']) ?></b></div>
        </div>
        
        <p><strong>Catatan Pengarah:</strong></p>
        <div style="background: #f1f5f9; padding: 15px; border-radius: 8px; min-height: 100px;">
            <?= nl2br(htmlspecialchars($row['catatan'] ?? 'Tiada catatan lagi.')) ?>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="homedirector.php" class="btn-back">⬅ Kembali ke Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>
