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
