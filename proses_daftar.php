<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil input
    $no_rujukan = $_POST['no_rujukan'];
    $tarikh_terima = $_POST['tarikh_terima'];
    $daripada = $_POST['daripada'];
    $perkara = $_POST['perkara'];
    $kolej = $_POST['kolej'];
    $target_role = $_POST['target_role'];
    
    // 2. Dapatkan Emel Penerima
    $stmt_email = $conn->prepare("SELECT email FROM users WHERE role = ? LIMIT 1");
    $stmt_email->execute([$target_role]);
    $user_data = $stmt_email->fetch(PDO::FETCH_ASSOC);
    $email_penerima = $user_data ? $user_data['email'] : null;
    
    if (!$email_penerima) die("Ralat: Tiada emel untuk role $target_role");

    // 3. Proses Fail ke Google Drive
    $drive_file_id = "GAGAL_UPLOAD";
    $base64_file = "";
    $file_name = "";

    if (isset($_FILES['fail_surat']) && $_FILES['fail_surat']['error'] == 0) {
        $file_name = $_FILES['fail_surat']['name'];
        $base64_file = base64_encode(file_get_contents($_FILES['fail_surat']['tmp_name']));
        $payload = json_encode(['fileData' => $base64_file, 'mimeType' => 'application/pdf', 'fileName' => $file_name]);
        
        $ch_drive = curl_init("https://script.google.com/macros/s/AKfycbyzLXkuCO7HCif_ESNPv8a96qwdW9v9zPCUSICJ9CKm_uPnAYStDBGgncZEsoGNQDEY/exec");
        curl_setopt($ch_drive, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch_drive, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_drive, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $drive_response = trim(curl_exec($ch_drive));
        curl_close($ch_drive);
        $drive_file_id = $drive_response;
    }

    // 4. Simpan ke Database
    // Kita tidak masukkan 'id' kerana ia akan dijana automatik oleh database
    $sql = "INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, target_role, status, drive_file_id) 
            VALUES (?, ?, ?, ?, ?, ?, 'BARU', ?)";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$no_rujukan, $tarikh_terima, $daripada, $perkara, $kolej, $target_role, $drive_file_id])) {
        echo "<script>alert('Surat telah didaftarkan!'); window.location='homeadmin.php';</script>";
    } else {
        $error = $stmt->errorInfo();
        echo "Ralat Database: " . $error[2];
    }
}
?>
