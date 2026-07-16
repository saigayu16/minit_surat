<?php
session_start();
include('db.php'); // Pastikan db.php menggunakan PDO

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = $_POST['username'] ?? '';
    $password_input = $_POST['password'] ?? '';
    $role_input     = $_POST['role'] ?? '';

    try {
        // Menggunakan nama lajur 'nama' berdasarkan DBeaver anda
        $stmt = $conn->prepare("SELECT * FROM users WHERE nama = :nama AND role = :role");
        $stmt->execute([
            'nama' => $username_input, 
            'role' => $role_input
        ]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Semakan log masuk (Menggunakan perbandingan teks biasa kerana data anda '12345')
        if ($user && $password_input === $user['password']) {
            
            // Set session
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_name']      = $user['nama'];
            $_SESSION['user_role']      = $user['role'];
            
            // Redirect mengikut peranan
            switch ($user['role']) {
                case 'admin': 
                    header("Location: homeadmin.php"); 
                    break;
                case 'pengarah': 
                    header("Location: homedirector.php"); 
                    break;
                case 'tpp': 
                    header("Location: hometpp.php"); 
                    break;
                case 'tpa': 
                    header("Location: hometpa.php"); 
                    break;
                default: 
                    header("Location: login.php"); 
                    break;
            }
            exit;
            
        } else {
            // Login gagal
            header("Location: login.php?error=1");
            exit;
        }

    } catch (PDOException $e) {
        // Jika ada ralat database, paparkan mesej (boleh dibuang untuk produksi)
        die("Ralat sistem: " . $e->getMessage());
    }
} else {
    // Jika akses direct ke auth.php tanpa POST
    header("Location: login.php");
    exit;
}
?>
