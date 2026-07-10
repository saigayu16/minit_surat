<?php
require_once __DIR__ . '/vendor/autoload.php';
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['surat_id'];
    $email_staf = $_POST['email'];
    $nama_staf = $_POST['nama_staf'];

    // 1. Semakan Staf dalam Database
    $stmt_check = $conn->prepare("SELECT email FROM staff WHERE email = ? AND nama = ?");
    $stmt_check->bind_param("ss", $email_staf, $nama_staf);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        echo "<script>alert('Ralat: Maklumat staf tidak sah!'); window.history.back();</script>";
        exit;
    }

    // 2. Setup API Brevo
    // GANTIKAN 'YOUR_BREVO_API_KEY_HERE' DENGAN KEY SEBENAR ANDA
    $apiKey = 'YOUR_BREVO_API_KEY_HERE'; 
    
    $config = SendinBlue\Client\Configuration::getDefaultConfiguration()
              ->setApiKey('api-key', $apiKey);
    
    $apiInstance = new SendinBlue\Client\Api\TransactionalEmailsApi(new GuzzleHttp\Client(), $config);

    try {
        $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail([
            'subject' => 'Notifikasi Minit Surat',
            'sender' => [
                'name' => 'Sistem Minit Digital', 
                'email' => 'saigayu1605@gmail.com' 
            ],
            'to' => [['email' => $email_staf, 'name' => $nama_staf]],
            'htmlContent' => "<html><body>Hai <strong>$nama_staf</strong>,<br><br>Anda telah dimaklumkan mengenai surat ini. Sila log masuk ke sistem untuk tindakan lanjut.</body></html>"
        ]);

        $apiInstance->sendTransacEmail($sendSmtpEmail);

        // 3. Kemaskini Database
        $stmt = $conn->prepare("UPDATE minit_surat SET status = 'DIMAKLUM', maklum_kepada = ? WHERE id = ?");
        $stmt->bind_param("si", $nama_staf, $id);
        $stmt->execute();

        echo "<script>alert('E-mel berjaya dihantar!'); window.location='homeadmin.php';</script>";
            
    } catch (Exception $e) {
        // Jika API Key salah atau limit habis, ia akan keluar alert ini
        $errorResponse = json_decode($e->getResponseBody(), true);
        $message = isset($errorResponse['message']) ? $errorResponse['message'] : $e->getMessage();
        echo "<script>alert('E-mel gagal: " . addslashes($message) . "'); window.history.back();</script>";
    }
}
?>
