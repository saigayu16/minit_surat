<?php
include('db.php');
$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM minit_surat WHERE id = $id");
$row = $result->fetch_assoc();

if (!$row) { die("Surat tidak dijumpai."); }

// Dapatkan nama fail dari database
$nama_fail = $row['fail_surat']; 
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Paparan Rasmi</title>
    <style>
        /* CSS anda (seperti sebelum ini) */
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .wrapper { display: grid; grid-template-columns: 1fr 400px; gap: 20px; max-width: 1200px; margin: auto; padding: 20px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <h3>📄 Dokumen Asal</h3>
        <?php if (!empty($nama_fail) && file_exists("uploads/" . $nama_fail)): ?>
            <iframe src="uploads/<?= htmlspecialchars($nama_fail) ?>" width="100%" height="700px"></iframe>
        <?php else: ?>
            <div style="padding: 50px; text-align: center; color: red;">
                Fail tidak ditemui di folder uploads/ (Nama fail: <?= htmlspecialchars($nama_fail) ?>)
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>BORANG MINIT</h2>
        <!-- Paparan maklumat lain -->
        <p>Rujukan: <?= htmlspecialchars($row['no_rujukan']) ?></p>
        <p>Didaftar: <?= $row['created_at'] ?></p>
        
        <!-- Navigasi -->
        <?php
        $prev = $conn->query("SELECT id FROM minit_surat WHERE id < $id ORDER BY id DESC LIMIT 1")->fetch_assoc();
        $next = $conn->query("SELECT id FROM minit_surat WHERE id > $id ORDER BY id ASC LIMIT 1")->fetch_assoc();
        ?>
        <a href="view_surat.php?id=<?= $prev['id'] ?? $id ?>">⬅ Sebelumnya</a> | 
        <a href="view_surat.php?id=<?= $next['id'] ?? $id ?>">Seterusnya ➡</a>
    </div>
</div>
</body>
</html>
