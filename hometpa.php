<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php'); // Pastikan db.php anda menggunakan 'new PDO(...)'

// 1. SEMAK SESI & ROLE TPA
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'tpa') {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'Timbalan Pengarah';
$user_role = "Timbalan Pengarah Akademik (TPA)";

// 2. KIRA STATISTIK TPA (Guna PDO)
$total_perlu_sahkan = 0;
$total_selesai = 0;
$total_semua = 0;

// Kira Dokumen Menunggu
$stmt1 = $conn->prepare("SELECT COUNT(*) as total FROM minit_surat WHERE target_role = 'tpa' AND status NOT LIKE ?");
$stmt1->execute(['%Sudah Disahkan%']);
$total_perlu_sahkan = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];

// Kira Dokumen Selesai
$stmt2 = $conn->prepare("SELECT COUNT(*) as total FROM minit_surat WHERE target_role = 'tpa' AND status LIKE ?");
$stmt2->execute(['%Sudah Disahkan%']);
$total_selesai = $stmt2->fetch(PDO::FETCH_ASSOC)['total'];

// Kira Jumlah Keseluruhan
$stmt3 = $conn->prepare("SELECT COUNT(*) as total FROM minit_surat WHERE target_role = 'tpa'");
$stmt3->execute();
$total_semua = $stmt3->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard TPA - Sistem Minit Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* [CSS ANDA KEKAL SAMA SEPERTI ASAL] */
        :root { --primary-color: #0f172a; --accent-sign: #2563eb; --accent-view: #ea580c; --bg-color: #f8fafc; --card-bg: #ffffff; --text-main: #ffffff; --text-muted: #64748b; --border-color: #e2e8f0; }
        body { font-family: 'Inter', sans-serif; background-image: linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.55)), url('homedirector.jpg'); background-repeat: no-repeat; background-size: cover; background-attachment: fixed; background-position: center center; margin: 0; padding: 0; color: var(--text-main); }
        .navbar { background: var(--primary-color); color: white; padding: 1.2rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .navbar h2 { margin: 0; font-size: 1.3rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .navbar h2 i { color: #fbbf24; }
        .user-info { display: flex; align-items: center; gap: 15px; font-size: 0.95rem; }
        .role-badge { background: #3b82f6; color: white; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; }
        .btn-logout { color: #f87171; text-decoration: none; font-weight: 600; transition: color 0.2s; }
        .container { max-width: 1300px; margin: 40px auto; padding: 0 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .stat-card { background: var(--card-bg); padding: 24px; border-radius: 12px; border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .stat-info h4 { margin: 0; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; }
        .stat-info p { margin: 8px 0 0 0; font-size: 1.8rem; font-weight: 700; color: #0f172a; }
        .stat-icon { font-size: 1.8rem; padding: 14px; border-radius: 10px; }
        .icon-sign { background: #eff6ff; color: #2563eb; } .icon-done { background: #f0fdf4; color: #16a34a; } .icon-all { background: #fef3c7; color: #d97706; }
        .table-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: var(--text-main); }
        .table-container { background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; color: var(--text-muted); padding: 16px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid var(--border-color); }
        td { padding: 16px; border-bottom: 1px solid var(--border-color); font-size: 0.95rem; color: #334155; }
        .status-badge { padding: 6px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .wait { background: #fee2e2; color: #991b1b; } .done { background: #d1fae5; color: #065f46; }
        .btn-action { padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; }
        .btn-view { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
        .btn-sign { background: #2563eb; color: white; }
    </style>
</head>
<body>

<nav class="navbar">
    <h2><i class="fa-solid fa-graduation-cap"></i> Sistem Minit Digital</h2>
    <div class="user-info">
        <span><i class="fa-solid fa-user-tie"></i> Tuan. <strong><?= htmlspecialchars($user_name) ?></strong></span>
        <span class="role-badge"><?= $user_role ?></span>
        | <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Log Keluar</a>
    </div>
</nav>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-info"><h4>Perlu Semakan</h4><p><?= $total_perlu_sahkan ?></p></div><div class="stat-icon icon-sign"><i class="fa-solid fa-file-signature"></i></div></div>
        <div class="stat-card"><div class="stat-info"><h4>Sudah Disahkan</h4><p><?= $total_selesai ?></p></div><div class="stat-icon icon-done"><i class="fa-solid fa-circle-check"></i></div></div>
        <div class="stat-card"><div class="stat-info"><h4>Jumlah</h4><p><?= $total_semua ?></p></div><div class="stat-icon icon-all"><i class="fa-solid fa-folder-open"></i></div></div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr><th>Rujukan</th><th>Admin</th><th>Kolej</th><th>Perkara</th><th>Status</th><th>Tindakan</th></tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT * FROM minit_surat WHERE target_role = 'tpa' ORDER BY id DESC");
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if ($rows) {
                    foreach ($rows as $row) {
                        $status = trim($row['status'] ?? 'BARU');
                        $is_done = stripos($status, 'Sudah Disahkan') !== false;
                        $badge = $is_done ? 'done' : 'wait';
                        echo "<tr>
                            <td>{$row['no_rujukan']}</td>
                            <td>{$row['didaftarkan_oleh']}</td>
                            <td>{$row['kolej']}</td>
                            <td>{$row['perkara']}</td>
                            <td><span class='status-badge $badge'>$status</span></td>
                            <td>" . ($is_done ? "<a href='view_surat.php?id={$row['id']}' class='btn-action btn-view'>Lihat</a>" : "<a href='tandatangan.php?id={$row['id']}' class='btn-action btn-sign'>Sahkan</a>") . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center;'>Tiada dokumen.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
