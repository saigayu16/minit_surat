<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_rujukan = mysqli_real_escape_string($conn, $_POST['no_rujukan']);
    $tarikh_terima = mysqli_real_escape_string($conn, $_POST['tarikh_terima']);
    $daripada = mysqli_real_escape_string($conn, $_POST['daripada']);
    $perkara = mysqli_real_escape_string($conn, $_POST['perkara']);
    $kolej = mysqli_real_escape_string($conn, $_POST['kolej']);
    $target_role = mysqli_real_escape_string($conn, $_POST['target_role']);
    
    // Ambil emel penerima berdasarkan role
    $stmt_email = $conn->prepare("SELECT email FROM users WHERE role = ? LIMIT 1");
    $stmt_email->bind_param("s", $target_role);
    $stmt_email->execute();
    $result = $stmt_email->get_result();
    
    if ($result->num_rows > 0) {
        $email_penerima = $result->fetch_assoc()['email'];
    } else {
        echo "<script>alert('Ralat: Tiada emel untuk role $target_role'); window.history.back();</script>";
        exit;
    }

    // Proses fail
    if (isset($_FILES['fail_surat']) && $_FILES['fail_surat']['error'] == 0) {
        $file_name = $_FILES['fail_surat']['name'];
        $file_data = file_get_contents($_FILES['fail_surat']['tmp_name']);
        $base64_file = base64_encode($file_data);
    } else {
        echo "<script>alert('Fail diperlukan.'); window.history.back();</script>";
        exit;
    }

    // Integrasi API (Manual cURL)
    $api_key = getenv('BREVO_API_KEY');
    $data = [
        "sender" => ["email" => "saigayu1605@gmail.com", "name" => "Sistem Minit Digital"],
        "to" => [["email" => $email_penerima]],
        "subject" => "Notifikasi: Surat Baharu - " . $no_rujukan,
        "htmlContent" => "Assalamualaikum, terdapat surat baharu untuk tindakan anda.",
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

    if ($http_code == 201) {
        $stmt = $conn->prepare("INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, target_role, status, fail_surat) VALUES (?, ?, ?, ?, ?, ?, 'BARU', ?)");
        $stmt->bind_param("sssssss", $no_rujukan, $tarikh_terima, $daripada, $perkara, $kolej, $target_role, $file_data);
        $stmt->execute();
        echo "<script>alert('Berjaya dihantar!'); window.location='homeadmin.php';</script>";
    } else {
        echo "<script>alert('E-mel gagal (Ralat: $http_code)'); window.history.back();</script>";
    }
}
?>
