<?php
// 1. Start output buffering to prevent header errors
ob_start(); 

session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 
    $role     = $_POST['role'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify user and password
    if ($user && $password === $user['password']) {
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_name']      = $user['username'];
        $_SESSION['user_role']      = $user['role'];
        $_SESSION['user_email']     = $user['email'];

        // Redirect based on role
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
                header("Location: login.php");
                break;
        }
        exit(); // Always exit after header redirect
    } else {
        // Redirect with error instead of using echo/alert
        header("Location: login.php?error=1");
        exit();
    }
} else {
    // Access denied if not POST
    header("Location: login.php");
    exit();
}
// End output buffering
ob_end_flush(); 
?>
