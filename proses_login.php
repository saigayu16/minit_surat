<?php
session_start();
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Semak password
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_name'] = $row['username'];
            $_SESSION['user_role'] = $row['role']; // Pastikan ada column 'role' (admin/pengarah)

            // REDIRECT BERDASARKAN ROLE
            if ($_SESSION['user_role'] == 'admin') {
                header("Location: index.php");
            } else {
                header("Location: home.php");
            }
            exit;
        } else {
            echo "<script>alert('Kata laluan salah!'); window.location='login.php';</script>";
        }
    } else {
        echo "<script>alert('Pengguna tidak dijumpai!'); window.location='login.php';</script>";
    }
}
?>