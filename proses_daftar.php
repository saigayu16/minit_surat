<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Semak jika data POST kosong disebabkan had saiz fail pelayan dilangkau
    if (empty($_POST) && empty($_FILES)) {
        die("Ralat: Saiz fail yang dimuat naik melebihi had yang dibenarkan oleh pelayan (Server POST limit). Sila semak fail php.ini.");
    }

    // 1. Ambil input dengan selamat dan HADKAN panjang aksara 
    // Nilai (50 atau 255) di bawah menghalang teks daripada melepasi had pangkalan data
    $no_rujukan = substr(mysqli_real_escape_string($conn, $_POST['no_rujukan'] ?? ''), 0, 50);
    $tarikh_terima = mysqli_real_escape_string($conn, $_POST['tarikh_terima'] ?? '');
    $daripada = substr(mysqli_real_escape_string($conn, $_POST['daripada'] ?? ''), 0, 255);
    $perkara = substr(mysqli_real_escape_string($conn, $_POST['perkara'] ?? ''), 0, 255);
    $kolej = substr(mysqli_real_escape_string($conn, $_POST['kolej'] ?? ''), 0, 50);
    $target_role = substr(mysqli_real_escape_string($conn, $_POST['target_role'] ?? ''), 0, 50);
    
    // 2. Dapatkan Emel Penerima Berdasarkan Role
    $stmt_email = $conn->prepare("SELECT email FROM users WHERE role = ? LIMIT 1");
    $stmt_email->bind_param("s", $target_role);
    $stmt_email->execute();
    $result = $stmt_email->get_result();
    $email_penerima = ($result->num_rows > 0) ? $result->fetch_assoc()['email'] : null;
    
    if (!$email_penerima) {
        die("Ralat: Tiada emel didaftarkan untuk peranan (role) $target_role");
    }

    // Tetapkan nilai awal
    $drive_file_id = "GAGAL_UPLOAD";
    $base64_file = null;
    $file_name = null;

    // 3. Proses Fail ke Google Drive
    if (isset($_FILES['fail_surat']) && $_FILES['fail_surat']['error'] == 0) {
        $file_name = $_FILES['fail_surat']['name'];
        $base64_file = base64_encode(file_get_contents($_FILES['fail_surat']['tmp_name']));
        $payload = json_encode(['fileData' => $base64_file, 'mimeType' => 'application/pdf', 'fileName' => $file_name]);
        
        $ch_drive = curl_init("https://script.google.com/macros/s/AKfycbyrdRJFIC8-56GxTjdpTjxRPEQjedujHE2OeirOuYr_74YUb9IZnXLNgAnm7oiHpa9i/exec");
        curl_setopt($ch_drive, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch_drive, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_drive, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch_drive, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $drive_response = trim(curl_exec($ch_drive));
        $http_code_drive = curl_getinfo($ch_drive, CURLINFO_HTTP_CODE);
        curl_close($ch_drive);

        if ($http_code_drive == 200 && strpos($drive_response, 'ERROR') === false) {
            $drive_file_id = $drive_response;
        }
    }

    // 4. Simpan ke Database Dahulu (Supaya kita dapat ID surat baru)
    $stmt = $conn->prepare("INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, kolej, target_role, status, drive_file_id) VALUES (?, ?, ?, ?, ?, ?, 'BARU', ?)");
    $stmt->bind_param("sssssss", $no_rujukan, $tarikh_terima, $daripada, $perkara, $kolej, $target_role, $drive_file_id);
    
    if ($stmt->execute()) {
        $id_surat_baru = $stmt->insert_id; // Ambil ID rekod yang baru dimasukkan

        // 5. Tentukan Halaman Dashboard Mengikut Logik Anda
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        
        $halaman_tujuan = ""; 
        $role = strtolower(trim($target_role)); // Tukar kepada huruf kecil untuk padanan selamat
        
        if (strpos($role, 'tpp') !== false) {
            $halaman_tujuan = "hometpp.php"; // Timbalan Pengarah Pengurusan
        } elseif (strpos($role, 'tpa') !== false) {
            $halaman_tujuan = "hometpa.php"; // Timbalan Pengarah Akademik
        } elseif (strpos($role, 'pengarah') !== false) {
            $halaman_tujuan = "homedirector.php"; // Pengarah
        }

        // Pastikan role sah sebelum meneruskan
        if (empty($halaman_tujuan)) {
            die("Ralat: Kategori peranan ('$target_role') tidak sah.");
        }

        // Gabungkan URL lengkap berserta ID surat ke fail dashboard khusus masing-masing
        $link_sistem = $protocol . "://$host/$halaman_tujuan?id=" . $id_surat_baru; 

        // 6. Integrasi API Brevo (E-mel dengan Butang Link Website Khusus)
        $api_key = getenv('BREVO_API_KEY');
        
        $data = [
            "sender" => ["email" => "saigayu1605@gmail.com", "name" => "Sistem Minit Digital"],
            "to" => [["email" => $email_penerima]],
            "subject" => "Notifikasi: Surat Baharu - " . $no_rujukan,
            "htmlContent" => "
                <p>Assalamualaikum wbt,</p>
                <p>Terdapat surat baharu dengan no rujukan <b>{$no_rujukan}</b> untuk tindakan anda.</p>
                <p>Sila klik butang di bawah untuk masuk ke dashboard anda dan menyemak surat:</p>
                <p><a href='{$link_sistem}' style='background: #f57c00; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Buka Dashboard Sistem</a></p>
                <p>Atau salin pautan ini ke pelayar anda: <br><a href='{$link_sistem}'>{$link_sistem}</a></p>
                <p>Sekian, terima kasih.</p>
            "
        ];

        // Sertakan lampiran PDF jika wujud
        if ($base64_file && $file_name) {
            $data["attachment"] = [["content" => $base64_file, "name" => $file_name]];
        }

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['api-key: ' . $api_key, 'Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);

        echo "<script>alert('Surat telah didaftarkan dan e-mel berjaya dihantar! (Drive ID: $drive_file_id)'); window.location='homeadmin.php';</script>";
    } else {
        echo "Ralat Database: " . $stmt->error;
    }
}
?>
