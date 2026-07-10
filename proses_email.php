<?php
// Letakkan ini paling atas untuk paksa sistem tunjukkan semua ralat
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['surat_id'];
    $email_staf = $_POST['email'];
    $nama_staf = $_POST['nama_staf'];

    // 1. Semakan Staf (CHECKPOINT 1)
    $stmt_check = $conn->prepare("SELECT email FROM staff WHERE email = ? AND nama = ?");
    $stmt_check->bind_param("ss", $email_staf, $nama_staf);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        die("Ralat: Staf tidak dijumpai.");
    }

    // 2. Setup Brevo (CHECKPOINT 2)
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
            'htmlContent' => "<html><body>Testing emel</body></html>"
        ]);

        // (CHECKPOINT 3)
        $apiInstance->sendTransacEmail($sendSmtpEmail);

        // Update DB
        $stmt = $conn->prepare("UPDATE minit_surat SET status = 'DIMAKLUM', maklum_kepada = ? WHERE id = ?");
        $stmt->bind_param("si", $nama_staf, $id);
        $stmt->execute();

        echo "<script>alert('Berjaya!'); window.location='homeadmin.php';</script>";
        exit; // Pastikan skrip berhenti di sini
            
    } catch (Exception $e) {
        // Paparkan ralat jika API gagal
        echo "<h1>Ralat API:</h1><pre>" . $e->getMessage() . "</pre>";
        echo "<br><a href='javascript:history.back()'>Kembali</a>";
        exit;
    }
}
?>
