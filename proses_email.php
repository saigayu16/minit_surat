<?php
// Aktifkan laporan ralat supaya kita tahu apa yang salah
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pastikan data POST sampai
    if (!isset($_POST['surat_id']) || !isset($_POST['email'])) {
        die("Ralat: Data borang tidak lengkap.");
    }

    $id = $_POST['surat_id'];
    $email_staf = $_POST['email'];
    $nama_staf = $_POST['nama_staf'];

    echo "Data diterima. Sedang memproses...<br>";

    // 1. Semakan Staf dalam Database
    $stmt_check = $conn->prepare("SELECT email FROM staff WHERE email = ? AND nama = ?");
    $stmt_check->bind_param("ss", $email_staf, $nama_staf);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        die("Ralat: Staf tidak wujud dalam database.");
    }
    echo "Staf dijumpai dalam database.<br>";

    // 2. Ambil API Key
    $apiKey = getenv('BREVO_API_KEY');
    if (!$apiKey) {
        die("Ralat: API Key (BREVO_API_KEY) tidak dijumpai di server. Sila semak setting Environment di Render.");
    }
    echo "API Key dijumpai. Sedang menyambung ke Brevo...<br>";
    
    $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
    $apiInstance = new SendinBlue\Client\Api\TransactionalEmailsApi(new GuzzleHttp\Client(), $config);

    try {
        $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail([
            'subject' => 'Notifikasi Minit Surat',
            'sender' => ['name' => 'Sistem Minit Digital', 'email' => 'saigayu1605@gmail.com'],
            'to' => [['email' => $email_staf, 'name' => $nama_staf]],
            'htmlContent' => "<html><body>Hai <strong>$nama_staf</strong>, anda telah dimaklumkan mengenai surat baru.</body></html>"
        ]);

        echo "Sedang menghantar...<br>";
        $apiInstance->sendTransacEmail($sendSmtpEmail);
        echo "Emel berjaya dihantar ke API!<br>";

        // 3. Kemaskini Database
        $stmt = $conn->prepare("UPDATE minit_surat SET status = 'DIMAKLUM', maklum_kepada = ? WHERE id = ?");
        $stmt->bind_param("si", $nama_staf, $id);
        $stmt->execute();

        echo "<script>alert('Berjaya!'); window.location='homeadmin.php';</script>";
            
    } catch (Exception $e) {
        echo "<b>RALAT API:</b> " . $e->getMessage();
    }
} else {
    echo "Bukan request POST.";
}
?>
