<?php
require_once __DIR__ . '/vendor/autoload.php'; // Ini akan mencari folder vendor tadi

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadDir = 'uploads/';
    $filePath = $uploadDir . basename($_FILES['file']['name']);
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        
        // --- KONFIGURASI GOOGLE DRIVE ---
        $client = new Google_Client();
        $client->setAuthConfig('credentials.json'); // Pastikan fail JSON anda ada di sini
        $client->addScope(Google_Service_Drive::DRIVE_FILE);
        
        $service = new Google_Service_Drive($client);
        
        // Buat metadata fail
        $fileMetadata = new Google_Service_Drive_DriveFile(['name' => $_FILES['file']['name']]);
        $content = file_get_contents($filePath);
        
        // Muat naik
        $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => 'application/pdf',
            'uploadType' => 'multipart'
        ]);
        
        echo json_encode(["message" => "PDF berjaya dihantar ke Google Drive!"]);
    } else {
        echo json_encode(["message" => "Ralat semasa menyimpan fail."]);
    }
}
?>