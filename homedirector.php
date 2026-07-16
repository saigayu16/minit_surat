<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Panggil fail sambungan DB (Pastikan db.php menggunakan PDO)
include('db.php'); 

// 1. SEMAK SESI & ROLE PENGARAH
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'pengarah') {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'Pengarah';
$user_role = "Pengarah";

// 2. KIRA STATISTIK (Menggunakan PDO)
$total_perlu_sahkan = 0;
$total_selesai = 0;
$total_kkkb = 0;

try {
    // Kira Dokumen Menunggu Pengesahan
    $stmt_wait = $conn->query("SELECT COUNT(*) as total FROM minit_surat WHERE status != 'SELESAI TANDATANGAN' AND status != 'DIMAKLUM'");
    $total_perlu_sahkan = $stmt_wait->fetchColumn();

    // Kira Dokumen Selesai
    $stmt_done = $conn->query("SELECT COUNT(*) as total FROM minit_surat WHERE status = 'SELESAI TANDATANGAN' OR status = 'DIMAKLUM'");
    $total_selesai = $stmt_done->fetchColumn();

    // Kira Jumlah Surat Kolej Komuniti Kepala Batas
    $stmt_kkkb = $conn->prepare("SELECT COUNT(*) as total FROM minit_surat WHERE kolej = :kolej");
    $stmt_kkkb->execute(['kolej' => 'Kolej Komuniti Kepala Batas']);
    $total_kkkb = $stmt_kkkb->fetchColumn();
} catch (PDOException $e) {
    // Biarkan 0 jika ada error
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengarah - Sistem Minit Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0f172a;
            --accent-sign: #2563eb;
            --accent-view: #ea580c;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #ffffff;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        body { font-family: 'Inter', sans-serif; background-image: linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.55)), url('homedirector.jpg'); background-repeat: no-repeat; background-size: cover; background-attachment: fixed; background-position: center center; margin: 0; padding: 0; color: var(--text-main); }
        .navbar { background: var(--primary-color); padding: 1.2rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .navbar h2 { margin: 0; font-size: 1.3rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .navbar h2 i { color: #fbbf24; }
        .user-info { display: flex; align-items: center; gap: 15px; font-size: 0.95rem; }
        .role-badge { background: #3b82f6; color: white; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; }
        .btn-logout { color: #f87171; text-decoration: none; font-weight: 600; }
        .container { max-width: 1300px; margin: 40px auto; padding: 0 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .stat-card { background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .stat-info h4 { margin: 0; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; }
        .stat-info p { margin: 8px 0 0 0; font-size: 1.8rem; font-weight: 700; color: #0f172a; }
        .stat-icon { font-size: 1.8rem; padding: 14px; border-radius: 10px; }
        .icon-sign { background: #eff6ff; color: #2563eb; } 
        .icon-done { background: #f0fdf4; color: #16a34a; } 
        .icon-kolej { background: #fef3c7; color: #d97706; }
        .table-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: white; }
        .table-container { background: var(--card-bg); border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid var(--border-color); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; color: var(--text-muted); padding: 16px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid var(--border-color); }
        td { padding: 16px; border-bottom: 1px solid var(--border-color); font-size: 0.95rem; color: #334155; }
        .status-badge { padding: 6px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; text-transform: uppercase; }
        .wait { background: #fee2e2; color: #991b1b; }
        .done { background: #d1fae5; color: #065f46; }
        .btn-action { padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .btn-view { background: #fff7ed; color: var(--accent-view); border: 1px solid #ffedd5; }
        .btn-sign { background: var(--accent-sign); color: white; }
    </style>
</head>
<body>

<nav class="navbar">
    <h2><i class="fa-solid fa-signature"></i> Sistem Minit Digital</h2>
    <div class="user-info">
        <span><i class="fa-solid fa-user-tie"></i> Tuan. <strong><?= htmlspecialchars($user_name) ?></strong></span>
        <span class="role-badge"><?= $user_role ?></span>
        | <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Log Keluar</a>
    </div>
</nav>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info"><h4>Perlu Kelulusan</h4><p><?= (int)$total_perlu_sahkan ?></p></div>
            <div class="stat-icon icon-sign"><i class="fa-solid fa-file-signature"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info"><h4>Selesai Disahkan</h4><p><?= (int)$total_selesai ?></p></div>
            <div class="stat-icon icon-done"><i class="fa-solid fa-circle-check"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info"><h4>Surat KKKB</h4><p><?= (int)$total_kkkb ?></p></div>
            <div class="stat-icon icon-kolej"><i class="fa-solid fa-school"></i></div>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr><th>Rujukan</th><th>Pendaftar</th><th>Asal Kolej</th><th>Perkara</th><th>Status</th><th>Tindakan</th></tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT * FROM minit_surat ORDER BY id DESC");
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $status = htmlspecialchars(trim($row['status'] ?? 'BARU'));
                    $is_done = (strcasecmp($status, 'SELESAI TANDATANGAN') == 0 || strcasecmp($status, 'DIMAKLUM') == 0);
                    $badge = $is_done ? 'done' : 'wait';
                    
                    echo "<tr>
                        <td style='font-family:monospace;'>".htmlspecialchars($row['no_rujukan'])."</td>
                        <td>".htmlspecialchars($row['didaftarkan_oleh'])."</td>
                        <td>".htmlspecialchars($row['kolej'])."</td>
                        <td style='max-width:300px;'>".htmlspecialchars($row['perkara'])."</td>
                        <td><span class='status-badge {$badge}'>{$status}</span></td>
                        <td>";
                        if ($is_done) {
                            echo '<a href="view_surat.php?id='.(int)$row['id'].'" class="btn-action btn-view"><i class="fa-solid fa-eye"></i> Lihat</a>';
                        } else {
                            echo '<a href="tandatangan.php?id='.(int)$row['id'].'" class="btn-action btn-sign"><i class="fa-solid fa-pen-nib"></i> Sahkan</a>';
                        }
                        echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
