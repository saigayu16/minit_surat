<?php
include('db.php');
$api_key = getenv('BREVO_API_KEY');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $surat_id    = $_POST['surat_id'];
    $nama_staf   = $_POST['nama_staf'];
    $email_staf  = $_POST['email'];

    // 1. Semak sama ada staf ini wujud dalam jadual 'staff'
    // Kita semak guna email untuk memastikan ia staf yang sah
    $stmt = $conn->prepare("SELECT id, nama FROM staff WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email_staf);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $staf_data = $result->fetch_assoc();
        
        // 2. Proses Fail (Base64 Encode)
        $file_name = $_FILES['dokumen_minit']['name'];
        $base64_file = base64_encode(file_get_contents($_FILES['dokumen_minit']['tmp_name']));

        // 3. Hantar E-mel guna Brevo
        $data = [
            "sender" => ["email" => "saigayu1605@gmail.com", "name" => "Sistem Minit Digital"],
            "to" => [["email" => $email_staf, "name" => $staf_data['nama']]],
            "subject" => "Notifikasi: Minit Surat Baharu",
            "htmlContent" => "Assalamualaikum " . $staf_data['nama'] . ", sila semak minit surat yang dilampirkan.",
            "attachment" => [["content" => $base64_file, "name" => $file_name]]
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['api-key: ' . $api_key, 'Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code >= 200 && $http_code < 300) {
            echo "<script>alert('E-mel berjaya dihantar kepada " . $staf_data['nama'] . "!'); window.location='homeadmin.php';</script>";
        } else {
            echo "Ralat penghantaran e-mel (HTTP $http_code).";
        }
    } else {
        echo "<script>alert('Ralat: Staf dengan e-mel $email_staf tidak ditemui dalam jadual staff!'); window.history.back();</script>";
    }
}
?>
