<?php
require_once('vendor/autoload.php'); // Pastikan library Brevo dimuat naik
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['surat_id'];
    $email_staf = $_POST['email'];
    $nama_staf = $_POST['nama_staf'];

    // 1. Semakan Staf (Mesti wujud dalam database)
    $stmt_check = $conn->prepare("SELECT email FROM staff WHERE email = ? AND nama = ?");
    $stmt_check->bind_param("ss", $email_staf, $nama_staf);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        echo "<script>alert('Ralat: Maklumat staf tidak sah!'); window.history.back();</script>";
        exit;
    }

    // 2. Setup API Brevo
    $config = SendinBlue\Client\Configuration::getDefaultConfiguration()
              ->setApiKey('api-key', getenv('BREVO_API_KEY'));
    $apiInstance = new SendinBlue\Client\Api\TransactionalEmailsApi(new GuzzleHttp\Client(), $config);

    try {
        $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail([
            'subject' => 'Notifikasi Minit Surat',
            'sender' => ['name' => 'Sistem Minit Digital', 'email' => 'sistem@minitdigital.com'],
            'to' => [['email' => $email_staf, 'name' => $nama_staf]],
            'htmlContent' => "<html><body>Hai <strong>$nama_staf</strong>,<br><br>Anda telah dimaklumkan mengenai surat ini. Sila log masuk ke sistem.</body></html>"
        ]);

        $apiInstance->sendTransacEmail($sendSmtpEmail);

        // 3. Kemaskini Database
        $stmt = $conn->prepare("UPDATE minit_surat SET status = 'DIMAKLUM', maklum_kepada = ? WHERE id = ?");
        $stmt->bind_param("si", $nama_staf, $id);
        $stmt->execute();

        echo "<script>alert('E-mel berjaya dihantar!'); window.location='homeadmin.php';</script>";
            
    } catch (Exception $e) {
        echo "<script>alert('E-mel gagal: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }
}
?>
