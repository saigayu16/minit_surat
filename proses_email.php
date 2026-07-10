<?php
// Letakkan ini paling atas untuk paksa sistem tunjukkan semua ralat jika ada masalah
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load libraries via Composer
require_once __DIR__ . '/vendor/autoload.php';
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil data dari form (Pastikan input name="nama" ada dalam HTML anda)
    $id = $_POST['surat_id'];
    $email_staf = $_POST['email'];
    $nama = $_POST['nama']; 

    // 2. Semakan Staf (CHECKPOINT 1)
    $stmt_check = $conn->prepare("SELECT email FROM staff WHERE email = ? AND nama = ?");
    $stmt_check->bind_param("ss", $email_staf, $nama);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        die("Ralat: Staf tidak dijumpai dalam rekod.");
    }

    // 3. Setup Brevo API (CHECKPOINT 2)
    $apiKey = getenv('BREVO_API_KEY');
    if (!$apiKey) {
        die("Ralat: BREVO_API_KEY tidak disetkan di Render Environment Variables.");
    }
    
    $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
    $apiInstance = new SendinBlue\Client\Api\TransactionalEmailsApi(new GuzzleHttp\Client(), $config);

    try {
        // 4. Hantar Emel via Brevo
        $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail([
            'subject' => 'Notifikasi Minit Surat',
            'sender' => ['name' => 'Sistem Minit Digital', 'email' => 'saigayu1605@gmail.com'],
            'to' => [['email' => $email_staf, 'name' => $nama]],
            'htmlContent' => "<html><body>Hai <strong>$nama</strong>,<br><br>Anda telah dimaklumkan mengenai surat ini. Sila rujuk sistem untuk dokumen minit.<br><br>Terima kasih.</body></html>"
        ]);

        $apiInstance->sendTransacEmail($sendSmtpEmail);

        // 5. Update Database (CHECKPOINT 3)
        $stmt = $conn->prepare("UPDATE minit_surat SET status = 'DIMAKLUM', maklum_kepada = ? WHERE id = ?");
        $stmt->bind_param("si", $nama, $id);
        $stmt->execute();

        // 6. Selesai
        echo "<script>alert('Berjaya! E-mel dihantar dan status surat dikemaskini.'); window.location='homeadmin.php';</script>";
        exit;
           
    } catch (Exception $e) {
        // Paparkan ralat jika API gagal
        echo "<h1>Ralat API Brevo:</h1><pre>" . $e->getMessage() . "</pre>";
        echo "<br><a href='javascript:history.back()'>Kembali</a>";
        exit;
    }
}
?>
