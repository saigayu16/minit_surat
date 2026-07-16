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

    // 3. Proses Fail
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
    // PENTING: PostgreSQL biasanya memerlukan 'DEFAULT' atau membiarkan ia kosong.
    // Jika ia masih NULL, kita kena masukkan ia secara eksplisit menggunakan NEXTVAL.
    
    $sql = "INSERT INTO minit_surat (id, no_rujukan, tarikh_terima, daripada, perkara, kolej, target_role, status, drive_file_id) 
            VALUES (DEFAULT, ?, ?, ?, ?, ?, ?, 'BARU', ?)";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$no_rujukan, $tarikh_terima, $daripada, $perkara, $kolej, $target_role, $drive_file_id])) {
        echo "<script>alert('Surat telah didaftarkan!'); window.location='homeadmin.php';</script>";
    } else {
        // Jika DEFAULT gagal, mungkin tiada sequence, kita cuba tanpa 'id' sama sekali
        $sql_alt = "INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, target_role, status, drive_file_id) 
                    VALUES (?, ?, ?, ?, ?, ?, 'BARU', ?)";
        $stmt_alt = $conn->prepare($sql_alt);
        if ($stmt_alt->execute([$no_rujukan, $tarikh_terima, $daripada, $perkara, $kolej, $target_role, $drive_file_id])) {
            echo "<script>alert('Surat telah didaftarkan!'); window.location='homeadmin.php';</script>";
        } else {
            echo "Ralat Database: " . $stmt_alt->errorInfo()[2];
        }
    }
}
?>
