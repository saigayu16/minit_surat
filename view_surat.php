<?php
include('db.php');
$id = intval($_GET['id']);
// Mengambil data surat termasuk tarikh/masa daftar
$result = $conn->query("SELECT * FROM minit_surat WHERE id = $id");
$row = $result->fetch_assoc();

if (!$row) { die("Surat tidak dijumpai."); }
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Paparan Rasmi - <?= htmlspecialchars($row['no_rujukan']) ?></title>
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; padding: 20px; }
        .wrapper { max-width: 1200px; margin: auto; display: grid; grid-template-columns: 1fr 400px; gap: 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .info-row { display: flex; margin-bottom: 10px; }
        .info-label { width: 150px; font-weight: bold; color: #4a5568; }
        .nav-btn { padding: 10px 15px; background: #2d3748; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="card">
        <h3>📄 Dokumen (Didaftar: <?= $row['created_at'] ?? 'Tiada masa' ?>)</h3>
        <iframe id="pdf_frame" width="100%" height="700px" style="border:none;"></iframe>
    </div>

    <div class="card">
        <h2>BORANG MINIT</h2>
        <div class="info-row"><div class="info-label">Rujukan:</div> <div><?= htmlspecialchars($row['no_rujukan']) ?></div></div>
        <div class="info-row"><div class="info-label">Didaftar:</div> <div><?= $row['created_at'] ?></div></div>
        
        <hr>
        
        <div style="display: flex; justify-content: space-between; margin-top: 20px;">
            <?php
            $prev = $conn->query("SELECT id FROM minit_surat WHERE id < $id ORDER BY id DESC LIMIT 1")->fetch_assoc();
            $next = $conn->query("SELECT id FROM minit_surat WHERE id > $id ORDER BY id ASC LIMIT 1")->fetch_assoc();
            ?>
            <a href="view_surat.php?id=<?= $prev['id'] ?? $id ?>" class="nav-btn">⬅ Sebelumnya</a>
            <a href="view_surat.php?id=<?= $next['id'] ?? $id ?>" class="nav-btn">Seterusnya ➡</a>
        </div>
        <br>
        <a href="homedirector.php" style="color: #4a5568;">⬅ Kembali ke Dashboard</a>
    </div>
</div>

<script>
    // Fetch ID fail untuk paparan auto
    fetch('papar_fail.php?id=<?= $id ?>')
    .then(r => r.text())
    .then(url => {
        if (url.startsWith("http")) document.getElementById('pdf_frame').src = url;
    });
</script>

</body>
</html>
