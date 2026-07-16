<?php
ob_start();
session_start();
include('db.php'); // Pastikan db.php anda menggunakan kod PDO yang saya beri sebelum ini

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password']; 
    $role     = $_POST['role'];

    // Gunakan PDO untuk query (serasi dengan PostgreSQL)
    $stmt = $conn->prepare("SELECT * FROM users WHERE nama = :username AND role = :role");
    $stmt->execute(['username' => $username, 'role' => $role]);
    $user = $stmt->fetch();

    // Verify user and password (Gunakan password_verify jika password di hash)
    // Jika password dalam database adalah plain text, gunakan: $password === $user['password']
    if ($user && ($password === $user['password'])) {
        session_regenerate_id(true);

        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_name']      = $user['nama']; // Tukar kepada 'nama' mengikut table Neon
        $_SESSION['user_role']      = $user['role'];
        $_SESSION['user_email']     = $user['email'];

        switch ($role) {
            case 'admin': header("Location: homeadmin.php"); break;
            case 'pengarah': header("Location: homedirector.php"); break;
            case 'tpp': header("Location: hometpp.php"); break;
            case 'tpa': header("Location: hometpa.php"); break;
            default: header("Location: login.php"); break;
        }
        exit();
    } else {
        header("Location: login.php?error=1");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
ob_end_flush();
?>
