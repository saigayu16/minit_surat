<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil data dari borang
    $no_rujukan = $_POST['no_rujukan'];
    $tarikh_terima = $_POST['tarikh_terima'];
    $daripada = $_POST['daripada'];
    $perkara = $_POST['perkara'];
    $kolej = $_POST['kolej'];
    $target_role = $_POST['target_role']; // Contoh: 'pengarah'
    
    // 2. Ambil emel penerima dari database berdasarkan role
    $stmt_email = $conn->prepare("SELECT email FROM users WHERE role = ? LIMIT 1");
    $stmt_email->bind_param("s", $target_role);
    $stmt_email->execute();
    $result = $stmt_email->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email_penerima = $row['email'];
    } else {
        echo "<script>alert('Ralat: Tiada emel ditemui untuk role $target_role'); window.history.back();</script>";
        exit;
    }

    // 3. Proses Fail (Simpan ke DB)
    if (isset($_FILES['fail_surat']) && $_FILES['fail_surat']['error'] == 0) {
        $file_data = file_get_contents($_FILES['fail_surat']['tmp_name']);
        $base64_file = base64_encode($file_data);
        $file_name = $_FILES['fail_surat']['name'];
    } else {
        echo "<script>alert('Fail diperlukan.'); window.history.back();</script>";
        exit;
    }

    // 4. Hantar Emel guna Brevo API
    $api_key = getenv('BREVO_API_KEY');
    $data = [
        "sender" => ["email" => "no-reply@sistemanda.com", "name" => "Sistem Minit Digital"],
        "to" => [["email" => $email_penerima]],
        "subject" => "Notifikasi: Surat Baharu - " . $no_rujukan,
        "htmlContent" => "Hai, terdapat surat baharu untuk tindakan anda ($target_role).<br><br>Perkara: $perkara",
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
        // Jika emel berjaya, baru insert ke database
        $stmt = $conn->prepare("INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, target_role, status) VALUES (?, ?, ?, ?, ?, ?, 'BARU')");
        $stmt->bind_param("ssssss", $no_rujukan, $tarikh_terima, $daripada, $perkara, $kolej, $target_role);
        $stmt->execute();
        
        echo "<script>alert('Berjaya dihantar kepada $target_role ($email_penerima)'); window.location='homeadmin.php';</script>";
    } else {
        echo "<script>alert('E-mel gagal dihantar. Ralat API: $http_code'); window.history.back();</script>";
    }
}
?>
