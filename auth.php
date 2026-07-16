<?php
session_start();
// Pastikan db.php anda tidak memaparkan sebarang 'echo' atau 'print' sebelum header() dipanggil
include('db.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_input = $_POST['username'] ?? '';
    $pass_input = $_POST['password'] ?? '';
    $role_input = $_POST['role'] ?? '';

    try {
        // Query mencari user berdasarkan nama dan role
        $stmt = $conn->prepare("SELECT * FROM users WHERE nama = :nama AND role = :role");
        $stmt->execute(['nama' => $nama_input, 'role' => $role_input]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Semak user dan password (teks biasa 12345)
        if ($user && $pass_input === $user['password']) {
            
            // Set Session
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect berdasarkan role
            switch ($user['role']) {
                case 'admin':
                    header("Location: homeadmin.php");
                    break;
                case 'pengarah':
                    header("Location: homedirector.php");
                    break;
                case 'tpa':
                    header("Location: hometpa.php");
                    break;
                case 'tpp':
                    header("Location: hometpp.php");
                    break;
                default:
                    header("Location: login.php?error=unknown_role");
                    break;
            }
            exit;

        } else {
            // Login gagal - Kembali ke login dengan error
            header("Location: login.php?error=1");
            exit;
        }

    } catch (PDOException $e) {
        // Jika DB gagal, paparkan ralat (hanya untuk tujuan debugging)
        die("Ralat Pangkalan Data: " . $e->getMessage());
    }
} else {
    header("Location: login.php");
    exit;
}
?>
