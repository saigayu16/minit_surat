<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load libraries via Composer
require_once __DIR__ . '/vendor/autoload.php';
include('db.php');
session_start();

$id = $_GET['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $surat_id = intval($_POST['surat_id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_staf']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Handle File Upload with safer path
    $attachmentPath = '';
    if (isset($_FILES['dokumen_minit']) && $_FILES['dokumen_minit']['error'] == 0) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!file_exists($uploadDir)) { mkdir($uploadDir, 0755, true); }
        
        $file_name = time() . "_" . basename($_FILES["dokumen_minit"]["name"]);
        $attachmentPath = $uploadDir . $file_name;
        
        if (!move_uploaded_file($_FILES["dokumen_minit"]["tmp_name"], $attachmentPath)) {
            die("Ralat: Gagal menyimpan fail. Sila semak kebenaran folder 'uploads'.");
        }
    }

    // Setup Brevo API
    $apiKey = getenv('BREVO_API_KEY');
    if (!$apiKey) { die("Ralat: BREVO_API_KEY tidak disetkan di Render."); }
    
    $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
    $apiInstance = new SendinBlue\Client\Api\TransactionalEmailsApi(new GuzzleHttp\Client(), $config);

    try {
        $emailData = [
            'subject' => 'Notifikasi Minit Surat Baru',
            'sender' => ['name' => 'Sistem Minit Digital', 'email' => 'saigayu1605@gmail.com'],
            'to' => [['email' => $email, 'name' => $nama]],
            'htmlContent' => "<html><body>Tuan/Puan <strong>$nama</strong>,<br><br>Anda telah dimaklumkan mengenai surat baru dalam Sistem Minit Digital.<br><br>Terima kasih.</body></html>"
        ];

        if ($attachmentPath && file_exists($attachmentPath)) {
            $emailData['attachment'] = [[
                'name' => basename($attachmentPath),
                'content' => base64_encode(file_get_contents($attachmentPath))
            ]];
        }

        $apiInstance->sendTransacEmail(new \SendinBlue\Client\Model\SendSmtpEmail($emailData));
        
        // Update Database
        $stmt = $conn->prepare("UPDATE minit_surat SET staf_dimaklumkan = ?, status = 'DIMAKLUM' WHERE id = ?");
        $stmt->bind_param("si", $nama, $surat_id);
        $stmt->execute();
        
        echo "<script>alert('Berjaya! E-mel telah dihantar.'); window.location='homeadmin.php?success=1';</script>";
        exit();
    } catch (Exception $e) {
        echo "<h1>Ralat API Brevo:</h1><pre>" . $e->getMessage() . "</pre>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maklum Kepada Staf</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Quicksand', sans-serif; background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('daftarsurat.jpg'); background-size: cover; background-position: center; background-attachment: fixed; background-repeat: no-repeat; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .box { background: #fff9c4; padding: 40px; border-radius: 2px 20px 2px 20px; width: 100%; max-width: 400px; box-shadow: 15px 15px 30px rgba(0,0,0,0.15); position: relative; transform: rotate(-2deg); transition: 0.3s; }
        .box:hover { transform: rotate(0deg) scale(1.02); }
        h3 { margin: 0 0 20px 0; color: #5d4037; text-align: center; font-weight: 700; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #795548; font-size: 0.9rem; font-weight: 600; }
        input, select { width: 100%; padding: 12px; border: 2px dashed #fbc02d; border-radius: 5px; background: rgba(255,255,255,0.4); box-sizing: border-box; font-family: inherit; }
        button { width: 100%; padding: 12px; background: #f57c00; color: white; border: none; border-radius: 50px; font-weight: 600; cursor: pointer; margin-top: 15px; transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        button:hover { background: #e65100; transform: scale(1.03); }
        .pin { width: 25px; height: 25px; background: radial-gradient(circle at 30% 30%, #ef5350, #b71c1c); border-radius: 50%; position: absolute; top: -10px; left: 50%; margin-left: -12.5px; box-shadow: 2px 2px 5px rgba(0,0,0,0.3); }
    </style>
</head>
<body>
    <div class="box">
        <div class="pin"></div>
        <h3><i class="fa-solid fa-note-sticky"></i> Nota Makluman</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="surat_id" value="<?= htmlspecialchars($id) ?>">
            <div class="form-group">
                <label>Pilih Staf:</label>
                <select name="nama_staf" id="staf_select" required onchange="updateEmail(this)">
                    <option value="">-- Pilih Nama Staf --</option>
                    <?php
                    $result = $conn->query("SELECT nama, email FROM staff");
                    while($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['nama']}' data-email='{$row['email']}'>{$row['nama']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>E-mel Staf:</label>
                <input type="email" name="email" id="email_input" readonly required>
            </div>
            <div class="form-group">
                <label>Muat Naik Minit (Pilihan):</label>
                <input type="file" name="dokumen_minit" accept=".pdf,.jpg,.png">
            </div>
            <button type="submit">Hantar Sekarang!</button>
        </form>
    </div>
    <script>
    function updateEmail(selectElement) {
        var selectedOption = selectElement.options[selectElement.selectedIndex];
        var email = selectedOption.getAttribute('data-email');
        document.getElementById('email_input').value = email;
    }
    </script>
</body>
</html>
