<?php
include('db.php');
$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM minit_surat WHERE id = $id");
$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Paparan Rasmi - <?= htmlspecialchars($row['no_rujukan'] ?? 'Tiada Rujukan') ?></title>
    <style>
        body { background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('background.jpg'); background-size: cover; background-position: center; background-attachment: fixed; font-family: 'Segoe UI', sans-serif; }
        .wrapper { max-width: 1200px; margin: auto; display: grid; grid-template-columns: 1fr 400px; gap: 20px; padding: 20px; }
        .card { background: rgba(255, 255, 255, 0.95); padding: 25px; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
        .minit-header { border-bottom: 3px solid #2d3748; padding-bottom: 10px; margin-bottom: 20px; }
        .info-row { display: flex; margin-bottom: 10px; }
        .info-label { width: 150px; font-weight: bold; color: #4a5568; }
        .signature-box { margin-top: 30px; padding-top: 20px; border-top: 1px dashed #cbd5e0; }
        .btn-back { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #4a5568; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="card">
        <h3>📄 Dokumen Asal</h3>
        <div id="loading-msg" style="padding: 20px; color: #555;">Memuatkan dokumen...</div>
        <iframe id="pdf_frame" width="100%" height="800px" style="border:none; display:none;"></iframe>
    </div>

    <div class="card">
        <div class="minit-header"><h2>BORANG MINIT</h2></div>
        <div class="info-row"><div class="info-label">Rujukan:</div> <div><?= htmlspecialchars($row['no_rujukan'] ?? '-') ?></div></div>
        <div class="info-row"><div class="info-label">Kolej:</div> <div><?= htmlspecialchars($row['kolej'] ?? '-') ?></div></div>
        <div class="info-row"><div class="info-label">Daripada:</div> <div><?= htmlspecialchars($row['daripada'] ?? '-') ?></div></div>
        <hr>
        
        <p><strong>Arahan & Catatan:</strong></p>
        <div style="background: #f0f7ff; padding: 15px; border-left: 5px solid #2563eb; border-radius: 4px; margin-bottom: 20px;">
            <?php if (!empty($row['arahan'])): ?>
                <div style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #bfdbfe;">
                    <strong style="color: #1e40af;">Arahan:</strong>
                    <div style="color: #1e293b; font-weight: 600; margin-top: 5px;"><?= htmlspecialchars($row['arahan']) ?></div>
                </div>
            <?php endif; ?>
            <div>
                <strong style="color: #475569;">Catatan Tambahan:</strong>
                <div style="margin-top: 5px; color: #334155;">
                    <?= !empty($row['catatan']) ? nl2br(htmlspecialchars($row['catatan'])) : '<em>Tiada catatan tambahan.</em>' ?>
                </div>
            </div>
        </div>

        <div class="signature-box">
            <p><strong>Disahkan Oleh:</strong> Pengarah</p>
            <?php if (!empty($row['tandatangan_data'])): ?>
                <img src="data:image/png;base64,<?= base64_encode($row['tandatangan_data']) ?>" width="150px" style="border:1px solid #ccc;">
            <?php else: ?>
                <p style="color:red;"><em>Belum disahkan</em></p>
            <?php endif; ?>
            <p><strong>Tarikh:</strong> <?= ($row['status'] == 'SELESAI') ? date('d/m/Y') : '-' ?></p>
        </div>

        <a href="homedirector.php" class="btn-back">⬅ Kembali ke Dashboard</a>
    </div>
</div>

<script>
    fetch('papar_fail.php?id=<?= $id ?>')
    .then(response => response.text())
    .then(url => {
        const loading = document.getElementById('loading-msg');
        const frame = document.getElementById('pdf_frame');
        
        if (url.trim() !== "ERROR" && url.startsWith("http")) {
            frame.src = url.trim();
            frame.style.display = "block";
            loading.style.display = "none";
        } else {
            loading.innerHTML = "<h3 style='color:red;'>Dokumen tidak dijumpai.</h3><p>ID Fail dalam database mungkin tidak sah atau fail telah dipadam di Drive.</p>";
        }
    })
    .catch(() => {
        document.getElementById('loading-msg').innerHTML = "Ralat semasa memuatkan dokumen.";
    });
</script>

</body>
</html>
