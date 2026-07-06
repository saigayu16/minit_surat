<?php
include('db.php');

// 1. Padam jadual lama jika wujud
$conn->query("DROP TABLE IF EXISTS users");

// 2. Cipta semula jadual dengan struktur yang betul
$sql_create = "CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL
)";

if ($conn->query($sql_create)) {
    echo "Jadual 'users' berjaya dicipta semula!<br>";

    // 3. Masukkan data Admin dan Pengarah dengan Password Hash
    $users = [
        ['user' => 'admin', 'pass' => 'admin123', 'role' => 'admin'],
        ['user' => 'pengarah', 'pass' => 'kolej123', 'role' => 'pengarah']
    ];

    foreach ($users as $u) {
        $hashed = password_hash($u['pass'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $u['user'], $hashed, $u['role']);
        
        if ($stmt->execute()) {
            echo "Akaun '{$u['role']}' (username: {$u['user']}) berjaya dicipta!<br>";
        }
    }
} else {
    echo "Ralat mencipta jadual: " . $conn->error;
}
?>