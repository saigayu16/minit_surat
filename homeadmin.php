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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #1e293b;
            --accent-color: #2563eb;
            --accent-hover: #1d4ed8;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #ffffff;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('homeadmin.jpg'); 
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            background-position: center center;
            margin: 0; 
            padding: 0;
            color: var(--text-main);
        }

        .navbar { 
            background: var(--primary-color); 
            color: white; 
            padding: 1.2rem 2rem; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .navbar h2 { margin: 0; font-size: 1.3rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .navbar h2 i { color: #38bdf8; }
        .user-info { display: flex; align-items: center; gap: 15px; font-size: 0.95rem; }
        .user-info .avatar-icon { background: rgba(255,255,255,0.1); padding: 8px 12px; border-radius: 5px; }
        .btn-logout { color: #f87171; text-decoration: none; font-weight: 600; transition: color 0.2s; }
        .btn-logout:hover { color: #ef4444; }

        .container { max-width: 1300px; margin: 40px auto; padding: 0 20px; }

        .admin-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .stat-card { background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .stat-info h4 { margin: 0; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .stat-info p { margin: 8px 0 0 0; font-size: 1.8rem; font-weight: 700; color: #0f172a; }
        .stat-icon { font-size: 2rem; padding: 12px; border-radius: 10px; }
        .icon-all { background: #eff6ff; color: #2563eb; }
        .icon-wait { background: #fef2f2; color: #dc2626; }
        .icon-done { background: #f0fdf4; color: #16a34a; }

        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section-header h3 { margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main); }

        .btn-add { background: var(--accent-color); color: white; padding: 11px 20px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2); }
        .btn-add:hover { background: var(--accent-hover); }

        .table-container { background: var(--card-bg); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid var(--border-color); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; color: var(--text-muted); padding: 16px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid var(--border-color); }
        td { padding: 16px; border-bottom: 1px solid var(--border-color); font-size: 0.95rem; color: #334155; }
        tr:hover td { background-color: #f8fafc; }

        .status-badge { padding: 6px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; text-transform: uppercase; }
        .wait { background: #fee2e2; color: #991b1b; }
        .done { background: #d1fae5; color: #065f46; }
        .dimaklum { background: #e0e7ff; color: #4338ca; }
        .action-link { text-decoration: none; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; margin: 2px 0; }
    </style>
</head>
<body>

<nav class="navbar">
    <h2><i class="fa-solid fa-folder-open"></i> Sistem Minit Digital</h2>
    <div class="user-info">
        <span class="avatar-icon"><i class="fa-solid fa-user-shield"></i> &nbsp;<?= htmlspecialchars($user_name) ?></span>
        | <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Log Keluar</a>
    </div>
</nav>

<div class="container">
    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-info"><h4>Jumlah Surat Masuk</h4><p><?= $total_surat ?></p></div>
            <div class="stat-icon icon-all"><i class="fa-solid fa-envelope"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info"><h4>Menunggu Tindakan</h4><p><?= $total_wait ?></p></div>
            <div class="stat-icon icon-wait"><i class="fa-solid fa-clock-rotate-left"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info"><h4>Selesai / Disahkan</h4><p><?= $total_done ?></p></div>
            <div class="stat-icon icon-done"><i class="fa-solid fa-circle-check"></i></div>
        </div>
    </div>

    <div class="section-header">
        <h3>Senarai Dokumen Terkini</h3>
        <a href="daftar_surat.php" class="btn-add"><i class="fa-solid fa-plus"></i> Daftar Surat Baru</a>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Tarikh</th>
                    <th>No. Rujukan</th>
                    <th>Daripada</th>
                    <th>Perkara</th>
                    <th>Didaftar Oleh</th>
                    <th>Status</th>
                    <th>Tindakan</th>
                    <th>Maklum Kepada</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM minit_surat ORDER BY id DESC");
                if ($res && $res->num_rows > 0) {
                    while($row = $res->fetch_assoc()) {
                        $status = trim($row['status'] ?? 'Menunggu Pengesahan');
                        $badge = (strcasecmp($status, 'DISAHKAN') == 0) ? 'done' : ((strcasecmp($status, 'DIMAKLUM') == 0) ? 'dimaklum' : 'wait');
                        
                        // Logik Tindakan
                        $tindakan = "<a href='view_surat.php?id={$row['id']}' class='action-link' style='color:#2563eb;'><i class='fa-solid fa-eye'></i> Lihat</a>";
                        
                        if (strcasecmp($status, 'DISAHKAN') == 0) {
                            $tindakan .= "<br><a href='cetak_minit.php?id={$row['id']}' target='_blank' class='action-link' style='color:#16a34a;'><i class='fa-solid fa-print'></i> Cetak</a>";
                        }

                        echo "<tr>
                            <td>".date('d/m/Y', strtotime($row['tarikh_terima']))."</td>
                            <td>".htmlspecialchars($row['no_rujukan'] ?? '-')."</td>
                            <td>".htmlspecialchars($row['daripada'] ?? '-')."</td>
                            <td>".htmlspecialchars($row['perkara'] ?? '-')."</td>
                            <td>".htmlspecialchars($row['didaftarkan_oleh'] ?? '-')."</td>
                            <td><span class='status-badge {$badge}'>{$status}</span></td>
                            <td><div style='display:flex; flex-direction:column;'>{$tindakan}</div></td>
                            <td>
                                <div style='display:flex; flex-direction:column; align-items:center;'>";
                                
                                if (!empty($row['staf_dimaklumkan'])) {
                                    echo "<span style='background:#e0f2fe; color:#0369a1; padding:4px 8px; border-radius:4px; font-size:0.75rem; font-weight:bold;'>
                                            <i class='fa-solid fa-user-check'></i> " . htmlspecialchars($row['staf_dimaklumkan']) . "
                                          </span>";
                                } else {
                                    echo "<a href='proses_maklum.php?id={$row['id']}' class='action-link' style='color:#7c3aed;'>
                                            <i class='fa-solid fa-paper-plane'></i> Maklum
                                          </a>";
                                }

                        echo "  </div>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' style='text-align:center; padding:30px;'>📂 Tiada dokumen ditemui.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php ob_end_flush(); ?>
