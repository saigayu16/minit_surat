<?php
require_once __DIR__ . '/vendor/autoload.php';

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client;

function hantarEmail($to_email, $to_name, $subject, $content, $attachment_base64 = null, $file_name = null) {
    $api_key = getenv('BREVO_API_KEY');
    
    $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $api_key);
    $apiInstance = new TransactionalEmailsApi(new Client(), $config);

    $data = [
        'sender' => ['email' => 'saigayu1605@gmail.com', 'name' => 'Sistem Minit Digital'],
        'to' => [['email' => $to_email, 'name' => $to_name]],
        'subject' => $subject,
        'htmlContent' => $content
    ];

    if ($attachment_base64 && $file_name) {
        $data['attachment'] = [['content' => $attachment_base64, 'name' => $file_name]];
    }

    $sendSmtpEmail = new SendSmtpEmail($data);

    try {
        $apiInstance->sendTransacEmail($sendSmtpEmail);
        return true;
    } catch (Exception $e) {
        error_log('Brevo API Error: ' . $e->getMessage());
        return false;
    }
}
?>
