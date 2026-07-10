<?php
require_once __DIR__ . '/vendor/autoload.php';
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['surat_id'];
    $email_staf = $_POST['email'];
    $nama_staf = $_POST['nama_staf'];

    // Ambil daripada Environment Variable (Sangat Selamat)
    $apiKey = getenv('BREVO_API_KEY');

    if (!$apiKey) {
        die("Ralat: API Key tidak dijumpai. Sila setkan BREVO_API_KEY di dashboard Render.");
    }

    $config = SendinBlue\Client\Configuration::getDefaultConfiguration()
              ->setApiKey('api-key', $apiKey);
    
    $apiInstance = new SendinBlue\Client\Api\TransactionalEmailsApi(new GuzzleHttp\Client(), $config);

    try {
        $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail([
            'subject' => 'Notifikasi Minit Surat',
            'sender' => ['name' => 'Sistem Minit Digital', 'email' => 'saigayu1605@gmail.com'],
            'to' => [['email' => $email_staf, 'name' => $nama_staf]],
            'htmlContent' => "<html><body>Hai <strong>$nama_staf</strong>, anda telah dimaklumkan mengenai surat baru. Sila log masuk ke sistem.</body></html>"
        ]);

        $apiInstance->sendTransacEmail($sendSmtpEmail);

        // Update database
        $stmt = $conn->prepare("UPDATE minit_surat SET status = 'DIMAKLUM', maklum_kepada = ? WHERE id = ?");
        $stmt->bind_param("si", $nama_staf, $id);
        $stmt->execute();

        echo "<script>alert('E-mel berjaya dihantar!'); window.location='homeadmin.php';</script>";
            
    } catch (Exception $e) {
        echo "<script>alert('E-mel gagal: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }
}
?>
