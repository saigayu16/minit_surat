<?php
session_start();
include('db.php'); 

// Semak Sesi Login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'Pengarah';

// Pengiraan Statistik
$total_perlu_sahkan = 0;
$total_selesai = 0;
$total_kepala_batas = 0;

$count_wait = $conn->query("SELECT COUNT(*) as total FROM minit_surat WHERE status != 'SELESAI' OR status IS NULL");
if($count_wait) $total_perlu_sahkan = $count_wait->fetch_assoc()['total'];

$count_done = $conn->query("SELECT COUNT(*) as total FROM minit_surat WHERE status = 'SELESAI'");
if($count_done) $total_selesai = $count_done->fetch_assoc()['total'];

$count_kkkb = $conn->query("SELECT COUNT(*) as total FROM minit_surat WHERE kolej = 'Kolej Komuniti Kepala Batas'");
if($count_kkkb) $total_kepala_batas = $count_kkkb->fetch_assoc()['total'];
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
        :root { --primary-color: #0f172a; --accent-sign: #2563eb; --accent-view: #ea580c; --bg-color: #f8fafc; --card-bg: #ffffff; --text-muted: #64748b; --border-color: #e2e8f0; }
        body { font-family: 'Inter', sans-serif; background-image: linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.55)), url('homedirector.jpg'); background-size: cover; margin: 0; padding: 0; color: #ffffff; }
        .navbar { background: var(--primary-color); padding: 1.2rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .container { max-width: 1300px; margin: 40px auto; padding: 0 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .stat-card { background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .stat-info h4 { margin: 0; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; }
        .stat-info p { margin: 8px 0 0 0; font-size: 1.8rem; font-weight: 700; color: #0f172a; }
        .stat-icon { font-size: 1.8rem; padding: 14px; border-radius: 10px; }
        .icon-sign { background: #eff6ff; color: #2563eb; } .icon-done { background: #f0fdf4; color: #16a34a; } .icon-kolej { background: #fef3c7; color: #d97706; }
        .table-container { background: var(--card-bg); border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); overflow: hidden; border: 1px solid var(--border-color); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; color: var(--text-muted); padding: 16px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid var(--border-color); }
        td { padding: 16px; border-bottom: 1px solid var(--border-color); font-size: 0.95rem; color: #334155; }
        .status-badge { padding: 6px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; text-transform: uppercase; }
        .wait { background: #fee2e2; color: #991b1b; } .done { background: #d1fae5; color: #065f46; }
        .btn-action { padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: all 0.2s; display: inline-block; }
        .btn-view { background: #fff7ed; color: var(--accent-view); border: 1px solid #ffedd5; }
        .btn-sign { background: var(--accent-sign); color: white; }
    </style>
</head>
<body>

<nav class="navbar">
    <h2><i class="fa-solid fa-signature"></i> Sistem Minit Digital</h2>
    <div>Tuan. <strong><?= htmlspecialchars($user_name) ?></strong> | <a href="logout.php" style="color:#f87171;">Log Keluar</a></div>
</nav>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-info"><h4>Perlu Kelulusan</h4><p><?= $total_perlu_sahkan ?></p></div><div class="stat-icon icon-sign"><i class="fa-solid fa-file-signature"></i></div></div>
        <div class="stat-card"><div class="stat-info"><h4>Selesai</h4><p><?= $total_selesai ?></p></div><div class="stat-icon icon-done"><i class="fa-solid fa-circle-check"></i></div></div>
        <div class="stat-card"><div class="stat-info"><h4>Surat KKKB</h4><p><?= $total_kepala_batas ?></p></div><div class="stat-icon icon-kolej"><i class="fa-solid fa-school"></i></div></div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Rujukan</th>
                    <th>Pendaftar</th>
                    <th>Kolej</th>
                    <th>Perkara</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM minit_surat ORDER BY id DESC");
                if ($res && $res->num_rows > 0) {
                    while($row = $res->fetch_assoc()) {
                        $status = trim($row['status'] ?? 'BARU');
                        $badge = (strcasecmp($status, 'SELESAI') == 0) ? 'done' : 'wait';
                        $display_status = (strcasecmp($status, 'SELESAI') == 0) ? 'SUDAH DISAHKAN' : $status;
                        $catatan_pendek = !empty($row['catatan']) ? htmlspecialchars(substr($row['catatan'], 0, 30)) . '...' : '-';

                        echo "<tr>
                            <td style='font-weight:600;'>".htmlspecialchars($row['no_rujukan'])."</td>
                            <td>".htmlspecialchars($row['didaftarkan_oleh'])."</td>
                            <td>".htmlspecialchars($row['kolej'])."</td>
                            <td>".htmlspecialchars($row['perkara'])."</td>
                            <td><span class='status-badge $badge'>$display_status</span></td>
                            <td style='font-style:italic; color:#64748b;'>$catatan_pendek</td>
                            <td>";
                        
                        $status = trim($row['status']); 

                    if (strcasecmp($status, 'SELESAI') == 0) {
                        // Jika status SELESAI, paparkan butang Lihat
                        echo '<a href="view_surat.php?id='.$row['id'].'" class="btn-action btn-view">Lihat</a>';
                    } else {
                        // Jika status BARU atau lain-lain, paparkan butang Sahkan
                        echo '<a href="tandatangan.php?id='.$row['id'].'" class="btn-action btn-sign">Sahkan</a>';
                    }
                        echo "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center; padding: 20px;'>Tiada dokumen ditemui.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
