<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['surat_id'];
    $email = $_POST['email'];
    $nama_staf = $_POST['nama_staf'];

    // 1. Semakan Staf
    $stmt_check = $conn->prepare("SELECT nama FROM staff WHERE email = ? AND nama = ?");
    $stmt_check->bind_param("ss", $email, $nama_staf);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        echo "<script>alert('Ralat: Maklumat staf tidak sah!'); window.history.back();</script>";
        exit;
    }

    // 2. Proses Fail
    if (isset($_FILES['dokumen_minit']) && $_FILES['dokumen_minit']['error'] == 0) {
        $file_data = file_get_contents($_FILES['dokumen_minit']['tmp_name']);
        $file_name = $_FILES['dokumen_minit']['name'];
        $base64_file = base64_encode($file_data);
    } else {
        echo "<script>alert('Fail diperlukan.'); window.history.back();</script>";
        exit;
    }

    // 3. Hantar E-mel guna Brevo API
    $api_key = getenv('BREVO_API_KEY'); // Pastikan anda set ini di Render Environment
    
    $data = [
        "sender" => ["email" => "no-reply@minitsurat.com", "name" => "Sistem Minit Digital"],
        "to" => [["email" => $email]],
        "subject" => "Notifikasi Minit Surat",
        "htmlContent" => "Hai <strong>$nama_staf</strong>,<br><br>Anda telah dimaklumkan mengenai surat ini. Sila rujuk dokumen minit yang dilampirkan.",
        "attachment" => [
            ["content" => $base64_file, "name" => $file_name]
        ]
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

    // 4. Semak status API (201 bermaksud berjaya)
    if ($http_code == 201) {
        $stmt = $conn->prepare("UPDATE minit_surat SET status = 'DIMAKLUM', maklum_kepada = ? WHERE id = ?");
        $stmt->bind_param("ss", $nama_staf, $id);
        $stmt->execute();

        echo "<script>alert('Berjaya dihantar!'); window.location='homeadmin.php';</script>";
    } else {
        echo "<script>alert('E-mel gagal (Ralat API: $http_code). Sila pastikan API Key betul.'); window.history.back();</script>";
    }
}
?>
