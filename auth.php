<?php
session_start();
include('db.php');

// Debugging: Check if database connection exists
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 
    $role     = $_POST['role'];

    // Use prepared statement with error handling
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    
    // IF THE QUERY FAILS, THIS WILL SHOW THE ERROR
    if ($stmt === false) {
        die("SQL Prepare Error: " . $conn->error);
    }

    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check login
    if ($user && $password === $user['password']) {
        session_regenerate_id(true);

        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_name']      = $user['username'];
        $_SESSION['user_role']      = $user['role'];
        $_SESSION['user_email']     = $user['email'] ?? ''; 

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
        exit();
    } else {
        echo "<script>alert('Nama pengguna, kata laluan, atau peranan salah!'); window.location='login.php';</script>";
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
