<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include('db.php');
    
    $username = trim($_POST['username']);
    $password = $_POST['password']; // Simpan terus tanpa hash (ikut keperluan anda)
    $role     = $_POST['role'];

    // Simpan ke DB secara terus
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);

    if ($stmt->execute()) {
        echo "<script>alert('Pendaftaran berjaya!'); window.location='login.php';</script>";
        exit;
    } else {
        echo "<script>alert('Ralat: " . addslashes($conn->error) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengguna - Sistem Minit Surat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0f172a; /* Gelap Eksekutif */
            --accent-color: #2563eb; /* Biru Korporat */
            --bg-color: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            /* SAMA SEPERTI MINIT SURAT: Menggunakan imej latar belakang dan overlay gelapkan sedikit */
            background-image: linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.75)), url('backgroundkkkb.jpg'); 
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            background-position: center center;
            margin: 0; 
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Kad Gaya Premium Kaca (Glassmorphism) */
        .register-card { 
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(12px); 
            padding: 40px 35px; 
            border-radius: 16px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.4); 
            width: 380px; 
            box-sizing: border-box;
            border: 1px solid rgba(255,255,255,0.2); 
            text-align: center;
        }

        /* Gaya Logo */
        .logo-container {
            margin-bottom: 20px;
        }
        
        .logo-kolej {
            height: 100px;
            width: auto;
            object-fit: contain;
        }

        h2 { 
            color: var(--primary-color); 
            margin: 0 0 5px 0; 
            font-weight: 700; 
            font-size: 1.6rem;
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.88rem;
            margin-bottom: 30px;
        }

        /* Input Sematan Ikon */
        .input-group {
            position: relative;
            margin-bottom: 18px;
            text-align: left;
        }

        .input-group i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1rem;
            z-index: 2;
        }

        input, select { 
            width: 100%; 
            padding: 12px 12px 12px 42px; 
            border: 1px solid var(--border-color); 
            border-radius: 8px; 
            background: #ffffff; 
            box-sizing: border-box; 
            font-size: 0.95rem;
            color: var(--text-main);
            transition: all 0.2s;
            font-family: inherit;
            position: relative;
        }

        /* Tetapan anak panah dropdown select yang bersih */
        select {
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 16px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        /* Butang Premium */
        button { 
            width: 100%; 
            padding: 13px; 
            margin-top: 10px; 
            background: var(--accent-color); 
            color: white; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 0.95rem;
            font-weight: 600; 
            transition: all 0.2s; 
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
        }

        button:hover { 
            background: #1d4ed8; 
            transform: translateY(-1px);
            box-shadow: 0 6px 12px rgba(37, 99, 235, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        /* Pautan Kembali */
        .back-link { 
            margin-top: 25px; 
            display: inline-flex; 
            align-items: center;
            gap: 6px;
            font-size: 0.85rem; 
            color: var(--text-muted); 
            text-decoration: none; 
            transition: color 0.2s;
            font-weight: 500;
        }

        .back-link:hover { 
            color: var(--accent-color); 
        }
    </style>
</head>
<body>

<div class="register-card">
    <div class="logo-container">
        <img src="logokkkb.png" alt="Logo Kolej" class="logo-kolej">
    </div>

    <h2>Daftar Pengguna</h2>
    <p class="subtitle">Sila lengkapkan maklumat akaun baharu</p>
    
    <form method="POST">
        <div class="input-group">
            <i class="fa-solid fa-user"></i>
            <input type="text" name="username" placeholder="Nama Pengguna" required>
        </div>
        
        <div class="input-group">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="password" placeholder="Kata Laluan" required>
        </div>
        
        <div class="input-group">
    <i class="fa-solid fa-user-shield"></i>
    <select name="role" required>
        <option value="" disabled selected hidden>Pilih Peranan...</option>
        <option value="admin">Admin</option>
        <option value="pengarah">Pengarah Kolej</option>
        <option value="tpp">Timbalan Pengarah Pengurusan</option>
        <option value="tpa">Timbalan Pengarah Akademik</option>
    </select>
</div>
        
        <button type="submit">Daftar Sekarang</button>
    </form>
    
    <a href="login.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Log Masuk
    </a>
</div>

</body>
</html>