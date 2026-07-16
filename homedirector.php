<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('db.php'); // Pastikan db.php menggunakan PDO

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
    $stmt_wait = $conn->query("SELECT COUNT(*) FROM minit_surat WHERE status != 'SELESAI TANDATANGAN' AND status != 'DIMAKLUM'");
    $total_perlu_sahkan = $stmt_wait->fetchColumn();

    $stmt_done = $conn->query("SELECT COUNT(*) FROM minit_surat WHERE status = 'SELESAI TANDATANGAN' OR status = 'DIMAKLUM'");
    $total_selesai = $stmt_done->fetchColumn();

    $stmt_kkkb = $conn->prepare("SELECT COUNT(*) FROM minit_surat WHERE kolej = ?");
    $stmt_kkkb->execute(['Kolej Komuniti Kepala Batas']);
    $total_kkkb = $stmt_kkkb->fetchColumn();
} catch (Exception $e) {
    // Abaikan ralat
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pengarah - Sistem Minit Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #0f172a; --accent-sign: #2563eb; --accent-view: #ea580c; --card-bg: #ffffff; --border-color: #e2e8f0; }
        body { font-family: 'Inter', sans-serif; background-image: linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.55)), url('homedirector.jpg'); background-size: cover; background-attachment: fixed; margin: 0; padding: 0; color: #ffffff; }
        .navbar { background: var(--primary-color); padding: 1.2rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .container { max-width: 1300px; margin: 40px auto; padding: 0 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .stat-card { background: var(--card-bg); padding: 24px; border-radius: 12px; border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; color: #334155; }
        .stat-info h4 { margin: 0; color: #64748b; font-size: 0.85rem; text-transform: uppercase; }
        .stat-info p { margin: 8px 0 0 0; font-size: 1.8rem; font-weight: 700; }
        .table-container { background: white; border-radius: 12px; overflow: hidden; color: #334155; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 16px; text-transform: uppercase; font-size: 0.8rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        td { padding: 16px; border-bottom: 1px solid var(--border-color); font-size: 0.95rem; }
        .btn-action { padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .btn-view { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
        .btn-sign { background: #2563eb; color: white; }
        .status-badge { padding: 4px 10px; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .done { background: #d1fae5; color: #065f46; }
        .wait { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<nav class="navbar">
    <h2><i class="fa-solid fa-signature"></i> Sistem Minit Digital</h2>
    <div>
        <span><i class="fa-solid fa-user-tie"></i> Tuan. <strong><?= htmlspecialchars($user_name) ?></strong></span>
        | <a href="logout.php" style="color: #f87171; text-decoration: none;">Log Keluar</a>
    </div>
</nav>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info"><h4>Perlu Kelulusan</h4><p><?= (int)$total_perlu_sahkan ?></p></div>
        </div>
        <div class="stat-card">
            <div class="stat-info"><h4>Selesai Disahkan</h4><p><?= (int)$total_selesai ?></p></div>
        </div>
        <div class="stat-card">
            <div class="stat-info"><h4>Surat KKKB</h4><p><?= (int)$total_kkkb ?></p></div>
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
                        <td>".htmlspecialchars($row['no_rujukan'])."</td>
                        <td>".htmlspecialchars($row['didaftarkan_oleh'] ?? '-')."</td>
                        <td>".htmlspecialchars($row['kolej'] ?? '-')."</td>
                        <td>".htmlspecialchars($row['perkara'] ?? '-')."</td>
                        <td><span class='status-badge {$badge}'>{$status}</span></td>
                        <td>";
                        if ($is_done) {
                            echo '<a href="view_surat.php?id='.(int)$row['id'].'" class="btn-action btn-view"><i class="fa-solid fa-eye"></i> Lihat</a>';
                        } else {
                            // Link ini akan menghantar pengguna ke tandatangan.php
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
