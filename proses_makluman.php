<?php
// Sambungan ke database
include('db.php');

// Dapatkan API KEY dari Environment Variable (Railway)
$api_key = getenv('BREVO_API_KEY');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil data dari borang
    $surat_id    = $_POST['surat_id'];
    $nama_staf   = $_POST['nama_staf'];
    $email_staf  = $_POST['email'];

    // 2. Semak kewujudan staf dalam jadual 'staff'
    $stmt = $conn->prepare("SELECT id, nama FROM staff WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email_staf);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $staf_data = $result->fetch_assoc();

        // 3. Proses Fail Lampiran (Mestilah ada fail yang dimuat naik)
        if (isset($_FILES['dokumen_minit']) && $_FILES['dokumen_minit']['error'] == 0) {
            $file_name = $_FILES['dokumen_minit']['name'];
            $file_content = file_get_contents($_FILES['dokumen_minit']['tmp_name']);
            $base64_file = base64_encode($file_content);
        } else {
            die("<script>alert('Ralat: Sila pastikan fail dimuat naik!'); window.history.back();</script>");
        }

        // 4. Integrasi API Brevo (Penghantaran E-mel)
        $data = [
            "sender" => ["email" => "saigayu1605@gmail.com", "name" => "Sistem Minit Digital"],
            "to" => [["email" => $email_staf, "name" => $staf_data['nama']]],
            "subject" => "Notifikasi: Minit Surat Baharu",
            "htmlContent" => "Assalamualaikum " . $staf_data['nama'] . ",<br><br>Sila semak minit surat yang dilampirkan untuk tindakan anda.<br><br>Terima kasih.",
            "attachment" => [["content" => $base64_file, "name" => $file_name]]
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'api-key: ' . $api_key, 
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 5. Semakan Status API
        if ($http_code >= 200 && $http_code < 300) {
            echo "<script>alert('Berjaya! E-mel telah dihantar kepada " . $staf_data['nama'] . "'); window.location='homeadmin.php';</script>";
        } else {
            // Log ralat untuk debug (401 biasanya salah API Key)
            echo "Ralat Penghantaran (HTTP $http_code): Sila semak semula API Key Brevo anda di Railway.";
        }
    } else {
        echo "<script>alert('Ralat: E-mel staf tidak ditemui dalam sistem.'); window.history.back();</script>";
    }
} else {
    header("Location: homeadmin.php");
}
?>
