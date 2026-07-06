<?php
session_start();
include('db.php');

if (!isset($_GET['id']) || empty($_GET['id'])) { die("Ralat: ID Dokumen tidak sah."); }
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM minit_surat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

$image_preview = 'previews/' . str_replace('.pdf', '.jpg', $row['fail_surat']);
$signature_img = !empty($row['tandatangan_fail']) ? 'uploads/' . $row['tandatangan_fail'] : "";
$tarikh_sah = !empty($row['tarikh_sah']) ? date('d/m/Y', strtotime($row['tarikh_sah'])) : date('d/m/Y');
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Minit Ceraian</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 20px; }
        .no-print-bar { text-align: center; margin-bottom: 20px; }
        .btn-drive-action { background-color: #2563eb; color: #fff; padding: 15px 30px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        #halamanDokumen { width: 210mm; min-height: 297mm; background: #fff; margin: 0 auto; position: relative; padding: 0; }
        #halamanDokumen img { width: 100%; display: block; }
        .floating-signature-card { position: absolute; bottom: 40px; right: 40px; border: 2px solid #000; background: #fff; padding: 10px; width: 200px; text-align: center; }
    </style>
</head>
<body>

<div class="no-print-bar">
    <button class="btn-drive-action" id="btnSimpanDrive" onclick="tangkapSkrinDanSimpan();">
        <i class="fa-solid fa-cloud-arrow-up"></i> SIMPAN KE GOOGLE DRIVE
    </button>
</div>

<div id="halamanDokumen">
    <img src="<?= $image_preview ?>" alt="Dokumen">
    <?php if (strcasecmp($row['status'], 'SELESAI') == 0): ?>
        <div class="floating-signature-card">
            <strong>DILULUSKAN DIGITAL</strong>
            <img src="<?= $signature_img ?>" style="width: 100px;">
            <p style="font-size: 10px;">TARIKH: <?= $tarikh_sah ?></p>
        </div>
    <?php endif; ?>
</div>

<script>
function tangkapSkrinDanSimpan() {
    var btn = document.getElementById('btnSimpanDrive');
    btn.disabled = true;
    btn.innerText = "Memproses...";

    // Pastikan imej dimuatkan sepenuhnya sebelum capture
    var imgElement = document.querySelector('#halamanDokumen img');
    
    html2canvas(document.getElementById('halamanDokumen'), { 
        useCORS: true, 
        allowTaint: false, // Penting: Tetapkan false
        logging: true,
        scale: 2 
    }).then(canvas => {
        // ... (kod jspdf anda seterusnya)
    });
}
        var imgData = canvas.toDataURL('image/jpeg', 1.0);
        const { jsPDF } = window.jspdf;
        var doc = new jsPDF('p', 'mm', 'a4');
        doc.addImage(imgData, 'JPEG', 0, 0, 210, 297);
        var pdfBase64 = doc.output('datauristring').split(',')[1];

        // Hantar ke simpan_drive.php
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "simpan_drive.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    var res = JSON.parse(xhr.responseText);
                    alert(res.message);
                } else {
                    alert("Ralat sistem backend: " + xhr.statusText);
                }
                btn.disabled = false;
                btn.innerText = "SIMPAN KE GOOGLE DRIVE";
            }
        };
        
        // Hantar fail dan ID supaya PHP tahu dokumen mana
        xhr.send("fail_baharu_base64=" + encodeURIComponent(pdfBase64) + "&id=<?= $id ?>");
    });
}
</script>
</body>
</html>