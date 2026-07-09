<?php
session_start();
include('db.php');

// 1. Semak Sesi Login
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

// 2. Semak ID Surat
if (!isset($_GET['id']) || empty($_GET['id'])) { die("ID tidak sah"); }
$id = intval($_GET['id']);

// 3. Dapatkan data surat
$res = $conn->query("SELECT * FROM minit_surat WHERE id = $id");
$surat = $res->fetch_assoc();

if (!$surat) { die("Dokumen tidak ditemui"); }
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Tandatangan Digital</title>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f1f5f9; padding: 20px; }
        .container { max-width: 1000px; margin: auto; display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 20px; }
        .panel { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        #signature-pad { 
            border: 2px dashed #cbd5e0; 
            width: 100%; 
            height: 300px; 
            border-radius: 8px; 
            cursor: crosshair; 
            background-color: #ffffff; 
            margin-bottom: 15px; 
            touch-action: none; 
            display: block;
        }
        .btn { padding: 12px 20px; cursor: pointer; border: none; border-radius: 6px; font-weight: bold; width: 48%; transition: opacity 0.2s; }
        textarea { width: 100%; height: 80px; padding: 10px; border: 1px solid #cbd5e0; border-radius: 6px; margin-bottom: 15px; }
        .sticky-note { background: #fef08a; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="panel">
        <h3><i class="fa-solid fa-file-pdf"></i> Dokumen Rujukan</h3>
        <iframe src="papar_fail.php?id=<?= $id ?>" width="100%" height="600px" style="border:none;"></iframe>
    </div>

    <div class="panel">
        <div class="sticky-note">
            <h4>Ringkasan Minit</h4>
            <p><strong>No Rujukan:</strong> <?= htmlspecialchars($surat['no_rujukan']) ?></p>
            <p><strong>Perkara:</strong> <?= htmlspecialchars($surat['perkara']) ?></p>
        </div>

        <label><strong>Catatan Pengarah:</strong></label>
        <textarea id="catatan" placeholder="Tulis ulasan di sini..."></textarea>
        
        <div class="arahan-container">
            <label><input type="checkbox" name="arahan" value="Untuk Tindakan"> Untuk Tindakan</label><br>
            <label><input type="checkbox" name="arahan" value="Untuk Makluman"> Untuk Makluman</label>
        </div>

        <p>Sila turunkan tandatangan:</p>
        <canvas id="signature-pad"></canvas>
        
        <div style="display: flex; justify-content: space-between;">
            <button class="btn" id="btn-clear" style="background:#e53e3e; color:white;">Padam</button>
            <button class="btn" id="save" style="background:#38a169; color:white;">Minit & Sahkan ke Drive</button>
        </div>
    </div>
</div>

<script>
    window.onload = function() {
        const canvas = document.getElementById('signature-pad');
        const signaturePad = new SignaturePad(canvas, { minWidth: 1, maxWidth: 3, penColor: "rgb(0, 0, 0)" });

        document.getElementById('btn-clear').addEventListener('click', () => signaturePad.clear());

        document.getElementById('save').addEventListener('click', function() {
            if (signaturePad.isEmpty()) { 
                alert("Sila turunkan tandatangan terlebih dahulu!"); 
                return; 
            }
            
            const btnSave = document.getElementById('save');
            btnSave.innerText = "Memproses...";
            btnSave.disabled = true;

            // 1. Kumpul Data
            const selected = [];
            document.querySelectorAll('input[name="arahan"]:checked').forEach((cb) => selected.push(cb.value));
            
            const dataToSend = {
                id: "<?= $id ?>",
                image: signaturePad.toDataURL('image/png'),
                catatan: document.getElementById('catatan').value,
                fileId: "<?= $surat['drive_file_id'] ?>", 
                folderId: "1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1",
                arahan: selected.join(', ')
            };

            // 2. Hantar ke Google Apps Script (JSON)
            fetch('https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec', {
                method: 'POST',
                mode: 'no-cors', // Penting untuk elak isu CORS
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataToSend)
            })
            .then(() => {
                // Selepas hantar ke Drive, hantar ke proses_tandatangan.php
                const fd = new FormData();
                fd.append('id', "<?= $id ?>");
                fd.append('catatan', dataToSend.catatan);
                fd.append('arahan_pilihan', dataToSend.arahan);

                fetch('proses_tandatangan.php', {
                method: 'POST',
                body: formData // Pastikan FormData anda mengandungi 'id'
            })
            .then(response => response.text())
            .then(result => {
                if (result.trim() === 'success') {
                    alert("Berjaya disahkan!");
                    window.location.href = 'homedirector.php'; // Redirect ke dashboard
                } else {
                    alert("Ralat: " + result);
                }
            });
            })
            .catch(error => {
                alert("Ralat: " + error);
                btnSave.innerText = "Minit & Sahkan ke Drive";
                btnSave.disabled = false;
            });
        });
    };
</script>
</body>
</html>
