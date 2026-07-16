<?php
session_start();
include('db.php'); // Pastikan db.php menggunakan PDO

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password']; // Ingat: gunakan password_verify jika anda simpan hash
    $role = $_POST['role'];

    // Gunakan PDO untuk query (PENTING untuk Neon)
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND role = :role");
    $stmt->execute(['username' => $username, 'role' => $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Semak password
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        
        // Redirect berdasarkan role
        switch ($role) {
            case 'admin': header("Location: homeadmin.php"); break;
            case 'pengarah': header("Location: homedirector.php"); break;
            // ... dan seterusnya
        }
    } else {
        header("Location: login.php?error=1");
    }
}
?>
