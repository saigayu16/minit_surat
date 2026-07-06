<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input untuk keselamatan
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 
    $role     = $_POST['role'];

    // Cari user berdasarkan username DAN role yang dipilih
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Semak jika user wujud dan password betul
    // Nota: Jika anda menggunakan password_hash() dalam database, 
    // tukar kepada: if ($user && password_verify($password, $user['password']))
    if ($user && $password === $user['password']) {
        session_regenerate_id(true);

        // Set session
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_name']      = $user['username'];
        $_SESSION['user_role']      = $user['role'];
        $_SESSION['user_email']     = $user['email']; // Penting untuk notifikasi emel

        // Hala tuju (Redirect) mengikut peranan yang diasingkan
        switch ($role) {
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
                // Default jika role tidak ditemui
                header("Location: login.php");
                break;
        }
        exit();
    } else {
        // Paparkan ralat jika login gagal
        echo "<script>alert('Nama pengguna, kata laluan, atau peranan salah!'); window.location='login.php';</script>";
        exit();
    }
} else {
    // Jika akses fail ini bukan melalui POST, hantar kembali ke login
    header("Location: login.php");
    exit();
}
?>