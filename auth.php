<?php
session_start();
include('db.php'); // Pastikan fail ini mengandungi sambungan PDO ke Neon

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil input dari form
    $nama_input = $_POST['username'] ?? '';
    $pass_input = $_POST['password'] ?? '';
    $role_input = $_POST['role'] ?? '';

    try {
        // 2. Query menggunakan PDO - pastikan nama lajur 'nama' (seperti dalam DBeaver anda)
        $stmt = $conn->prepare("SELECT * FROM users WHERE nama = :nama AND role = :role");
        $stmt->execute(['nama' => $nama_input, 'role' => $role_input]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Semak jika user wujud dan password betul (dalam DBeaver anda, password adalah teks biasa '12345')
        if ($user && $pass_input === $user['password']) {
            
            // 4. Set Session
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['user_role'] = $user['role'];
            
            // 5. Redirect berdasarkan role
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
            exit; // Penting untuk menghentikan skrip selepas redirect

        } else {
            // Log masuk gagal
            header("Location: login.php?error=1");
            exit;
        }

    } catch (PDOException $e) {
        // Paparkan ralat jika ada masalah sambungan ke Neon
        die("Ralat Pangkalan Data: " . $e->getMessage());
    }
} else {
    // Jika akses terus ke fail auth.php, hantar ke login
    header("Location: login.php");
    exit;
}
?>
