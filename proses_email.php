<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// This loads the libraries installed by Composer
require_once __DIR__ . '/vendor/autoload.php';
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['surat_id'];
    $email_staf = $_POST['email'];
    $nama_staf = $_POST['nama_staf'];

    // 1. Verify Staff
    $stmt_check = $conn->prepare("SELECT email FROM staff WHERE email = ? AND nama = ?");
    $stmt_check->bind_param("ss", $email_staf, $nama_staf);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        die("Ralat: Staf tidak dijumpai.");
    }

    // 2. Setup Brevo
    $apiKey = getenv('BREVO_API_KEY');
    if (!$apiKey) {
        die("Ralat: BREVO_API_KEY tidak disetkan di Render.");
    }
    
    $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
    $apiInstance = new SendinBlue\Client\Api\TransactionalEmailsApi(new GuzzleHttp\Client(), $config);

    try {
        $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail([
            'subject' => 'Notifikasi Minit Surat',
            'sender' => ['name' => 'Sistem Minit Digital', 'email' => 'saigayu1605@gmail.com'],
            'to' => [['email' => $email_staf, 'name' => $nama_staf]],
            'htmlContent' => "<html><body>Hai <strong>$nama_staf</strong>,<br><br>Anda telah dimaklumkan mengenai surat ini. Sila rujuk sistem.<br><br>Terima kasih.</body></html>"
        ]);

        $apiInstance->sendTransacEmail($sendSmtpEmail);

        // 3. Update Database
        $stmt = $conn->prepare("UPDATE minit_surat SET status = 'DIMAKLUM', maklum_kepada = ? WHERE id = ?");
        $stmt->bind_param("si", $nama_staf, $id);
        $stmt->execute();

        echo "<script>alert('Berjaya!'); window.location='homeadmin.php';</script>";
        exit;
    } catch (Exception $e) {
        echo "<h1>Ralat API:</h1><pre>" . $e->getMessage() . "</pre>";
        exit;
    }
}
?>
