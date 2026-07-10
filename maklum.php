<?php
// 1. Error reporting to help you debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Load the required libraries (Brevo & Guzzle)
require_once __DIR__ . '/vendor/autoload.php';
include('db.php');
session_start();

$id = $_GET['id'] ?? '';

// Proses bila borang dihantar
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $surat_id = intval($_POST['surat_id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_staf']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // 3. Handle File Upload
    $attachmentPath = '';
    if (isset($_FILES['dokumen_minit']) && $_FILES['dokumen_minit']['error'] == 0) {
        if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }
        $file_name = time() . "_" . basename($_FILES["dokumen_minit"]["name"]);
        $attachmentPath = "uploads/" . $file_name;
        move_uploaded_file($_FILES["dokumen_minit"]["tmp_name"], $attachmentPath);
    }

    // 4. Setup Brevo API
    $apiKey = getenv('BREVO_API_KEY');
    if (!$apiKey) { die("Ralat: BREVO_API_KEY tidak disetkan di Render."); }
    
    $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
    $apiInstance = new SendinBlue\Client\Api\TransactionalEmailsApi(new GuzzleHttp\Client(), $config);

    try {
        // Prepare Email Data
        $emailData = [
            'subject' => 'Notifikasi Minit Surat Baru',
            'sender' => ['name' => 'Sistem Minit Digital', 'email' => 'saigayu1605@gmail.com'],
            'to' => [['email' => $email, 'name' => $nama]],
            'htmlContent' => "<html><body>Tuan/Puan <strong>$nama</strong>,<br><br>Anda telah dimaklumkan mengenai surat baru dalam Sistem Minit Digital. Sila semak sistem.<br><br>Terima kasih.</body></html>"
        ];

        // Add attachment if file was uploaded
        if ($attachmentPath && file_exists($attachmentPath)) {
            $emailData['attachment'] = [[
                'name' => basename($attachmentPath),
                'content' => base64_encode(file_get_contents($attachmentPath))
            ]];
        }

        // Send Email via API
        $apiInstance->sendTransacEmail(new \SendinBlue\Client\Model\SendSmtpEmail($emailData));
        
        // 5. Update Database
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
    <title>Maklum Kepada Staf</title>
    </head>
<body>
    <div class="box">
        <h3>Nota Makluman</h3>
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
