<?php
include('db.php');
include('mailer.php'); // Panggil fail mailer anda

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_staf = $_POST['email'];
    $nama_staf  = $_POST['nama_staf'];

    // 1. Semak staf dalam database
    $stmt = $conn->prepare("SELECT nama FROM staff WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email_staf);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $staf = $result->fetch_assoc();
        
        // 2. Sediakan fail
        $file_name = $_FILES['dokumen_minit']['name'];
        $base64 = base64_encode(file_get_contents($_FILES['dokumen_minit']['tmp_name']));

        // 3. Panggil fungsi hantarEmail dari mailer.php
        $berjaya = hantarEmail(
            $email_staf, 
            $staf['nama'], 
            "Notifikasi: Minit Surat Baharu", 
            "Assalamualaikum " . $staf['nama'] . ", sila semak minit surat dilampirkan.",
            $base64,
            $file_name
        );

        if ($berjaya) {
            echo "<script>alert('E-mel berjaya dihantar!'); window.location='homeadmin.php';</script>";
        } else {
            echo "E-mel gagal dihantar. Sila semak API Key Brevo di Railway.";
        }
    } else {
        echo "Staf tidak dijumpai.";
    }
}
?>
