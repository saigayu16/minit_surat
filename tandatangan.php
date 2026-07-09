<?php
include('db.php');
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Tandatangan Pengarah</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/4.1.7/signature_pad.umd.min.js"></script>
    <style>
        .sig-container { border: 2px dashed #cbd5e1; width: 400px; height: 200px; margin: 20px 0; }
        canvas { width: 100%; height: 100%; cursor: crosshair; }
        .btn { padding: 10px 20px; cursor: pointer; border: none; border-radius: 4px; }
        .btn-save { background: #10b981; color: white; }
    </style>
</head>
<body>

<h3>Tandatangan untuk ID: <?= $id ?></h3>
<div class="sig-container">
    <canvas id="signature-pad"></canvas>
</div>
<button id="clear" class="btn">Padam</button>
<button id="save" class="btn btn-save">SIMPAN TANDATANGAN</button>

<script>
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas);

    document.getElementById('clear').addEventListener('click', () => signaturePad.clear());

    document.getElementById('save').addEventListener('click', () => {
        if (signaturePad.isEmpty()) { alert("Sila tandatangan dahulu!"); return; }

        const dataURL = signaturePad.toDataURL();
        
        // Hantar ke server menggunakan fetch
        fetch('simpan_signature.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=<?= $id ?>&image=' + encodeURIComponent(dataURL)
        })
        .then(response => response.text())
        .then(data => {
            alert("Tandatangan berjaya disimpan!");
            window.location.href = 'cetak_minit.php?id=<?= $id ?>';
        });
    });
</script>

</body>
</html>
