<?php 
// Start output buffering to prevent "headers already sent" errors
ob_start();

session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php'); 

// 1. SEMAK SESI
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. AMBIL NAMA USER
$user_name = $_SESSION['user_name'] ?? 'Admin Sistem';

// 3. KIRA STATISTIK
$total_surat = 0;
$total_wait = 0;
$total_done = 0;

$count_all = $conn->query("SELECT COUNT(*) as total FROM minit_surat");
if($count_all) $total_surat = $count_all->fetch_assoc()['total'];

$count_wait = $conn->query("SELECT COUNT(*) as total FROM minit_surat WHERE status != 'DISAHKAN'");
if($count_wait) $total_wait = $count_wait->fetch_assoc()['total'];

$count_done = $conn->query("SELECT COUNT(*) as total FROM minit_surat WHERE status = 'DISAHKAN'");
if($count_done) $total_done = $count_done->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Minit Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #1e293b;
            --accent-color: #2563eb;
            --accent-hover: #1d4ed8;
            --card-bg: #ffffff;
            --text-main: #ffffff;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('homeadmin.jpg'); 
            background-size: cover; background-attachment: fixed; background-position: center; margin: 0; padding: 0; color: var(--text-main);
        }

        .navbar { background: var(--primary-color); color: white; padding: 1.2rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .container { max-width: 1300px; margin: 40px auto; padding: 0 20px; }
        .admin-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .stat-card { background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .stat-info h4 { margin: 0; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; }
        .stat-info p { margin: 8px 0 0 0; font-size: 1.8rem; font-weight: 700; color: #0f172a; }
        .btn-add { background: var(--accent-color); color: white; padding: 11px 20px; text-decoration: none; border-radius: 6px; font-weight: 600; }
        .table-container { background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; color: var(--text-muted); padding: 16px; font-size: 0.85rem; text-transform: uppercase; }
        td { padding: 16px; border-bottom: 1px solid var(--border-color); color: #334155; }
        .status-badge { padding: 6px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
        .done { background: #d1fae5; color: #065f46; }
        .dimaklum { background: #e0e7ff; color: #4338ca; }
        .wait { background: #fee2e2; color: #991b1b; }
        .action-link { text-decoration: none; font-size: 0.85rem; font-weight: 600; }
    </style>
</head>
<body>

<nav class="navbar">
    <h2><i class="fa-solid fa-folder-open"></i> Sistem Minit Digital</h2>
    <div><?= htmlspecialchars($user_name) ?> | <a href="logout.php" style="color:#f87171;">Log Keluar</a></div>
</nav>

<div class="container">
    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-info"><h4>Jumlah Surat</h4><p><?= $total_surat ?></p></div>
        </div>
        <div class="stat-card">
            <div class="stat-info"><h4>Menunggu</h4><p><?= $total_wait ?></p></div>
        </div>
        <div class="stat-card">
            <div class="stat-info"><h4>Selesai</h4><p><?= $total_done ?></p></div>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Tarikh</th><th>Rujukan</th><th>Daripada</th><th>Status</th><th>Tindakan</th><th>Maklum Kepada</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM minit_surat ORDER BY id DESC");
                while($row = $res->fetch_assoc()) {
                    $status = trim($row['status'] ?? 'Menunggu Pengesahan');
                    $badge = (strcasecmp($status, 'DISAHKAN') == 0) ? 'done' : ((strcasecmp($status, 'DIMAKLUM') == 0) ? 'dimaklum' : 'wait');
                    
                    echo "<tr>
                        <td>".date('d/m/Y', strtotime($row['tarikh_terima']))."</td>
                        <td>".htmlspecialchars($row['no_rujukan'] ?? '-')."</td>
                        <td>".htmlspecialchars($row['daripada'] ?? '-')."</td>
                        <td><span class='status-badge {$badge}'>{$status}</span></td>
                        <td><a href='view_surat.php?id={$row['id']}'>Lihat</a></td>
                        <td>
                            <div style='display:flex; flex-direction:column; align-items:center; gap:8px;'>";
                            
                            if (!empty($row['maklum_kepada'])) {
                                echo "<span style='background:#e0f2fe; color:#0369a1; padding:4px 8px; border-radius:4px; font-size:0.75rem; font-weight:bold;'>
                                        <i class='fa-solid fa-user-check'></i> " . htmlspecialchars($row['maklum_kepada']) . "
                                      </span>
                                      <a href='cetak_minit.php?id={$row['id']}' target='_blank' style='background:#16a34a; color:white; padding:4px 10px; border-radius:4px; font-size:0.75rem; text-decoration:none;'>
                                        <i class='fa-solid fa-print'></i> Cetak
                                      </a>";
                            } else {
                                echo "<a href='maklum.php?id={$row['id']}' class='action-link' style='color:#7c3aed;'>
                                        <i class='fa-solid fa-paper-plane'></i> Maklum
                                      </a>";
                            }

                    echo "  </div>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php ob_end_flush(); ?>
