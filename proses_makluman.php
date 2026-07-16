<?php
include('db.php');
$api_key = getenv('BREVO_API_KEY');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $surat_id    = $_POST['surat_id'];
    $nama_staf   = $_POST['nama_staf'];
    $email_staf  = $_POST['email'];

    // 1. Semak emel dalam database (Pastikan staf wujud)
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email_staf);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 2. Proses Fail (Upload ke Google Drive/Base64)
        $file_name = $_FILES['dokumen_minit']['name'];
        $base64_file = base64_encode(file_get_contents($_FILES['dokumen_minit']['tmp_name']));

        // 3. Hantar E-mel guna Brevo
        $data = [
            "sender" => ["email" => "saigayu1605@gmail.com", "name" => "Sistem Minit Digital"],
            "to" => [["email" => $email_staf]],
            "subject" => "Notifikasi: Minit Surat Baharu",
            "htmlContent" => "Assalamualaikum $nama_staf, sila semak minit surat yang dilampirkan.",
            "attachment" => [["content" => $base64_file, "name" => $file_name]]
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['api-key: ' . $api_key, 'Content-Type: application/json']);
        
        $response = curl_exec($ch);
        curl_close($ch);

        echo "<script>alert('E-mel berjaya dihantar kepada $nama_staf!'); window.location='homeadmin.php';</script>";
    } else {
        echo "<script>alert('Ralat: Emel staf tidak ditemui dalam sistem!'); window.history.back();</script>";
    }
}
?>
