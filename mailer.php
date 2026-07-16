<?php
// mailer.php
require_once __DIR__ . '/vendor/autoload.php';

function hantarEmail($to_email, $to_name, $subject, $content, $attachment_base64 = null, $file_name = null) {
    $api_key = getenv('BREVO_API_KEY');
    
    $data = [
        "sender" => ["email" => "saigayu1605@gmail.com", "name" => "Sistem Minit Digital"],
        "to" => [["email" => $to_email, "name" => $to_name]],
        "subject" => $subject,
        "htmlContent" => $content
    ];

    // Jika ada lampiran (attachment), tambah ke dalam data
    if ($attachment_base64 && $file_name) {
        $data["attachment"] = [["content" => $attachment_base64, "name" => $file_name]];
    }

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['api-key: ' . $api_key, 'Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Return true jika berjaya (HTTP 201 Created)
    return ($http_code == 201);
}
?>
