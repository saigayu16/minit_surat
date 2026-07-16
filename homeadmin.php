<?php
// Start output buffering
ob_start();
session_start();

// Panggil fail sambungan DB (Pastikan db.php anda menggunakan PDO)
include('db.php'); 

// 1. SEMAK SESI
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. AMBIL NAMA USER
$user_name = $_SESSION['user_name'] ?? 'Admin';
$user_role = "Admin";

// 3. KIRA STATISTIK (Menggunakan PDO)
try {
    // Jumlah semua surat
    $total_surat = $conn->query("SELECT COUNT(*) FROM minit_surat")->fetchColumn();

    // Kira 'Menunggu'
    $stmt_wait = $conn->query("SELECT COUNT(*) FROM minit_surat WHERE status != 'SELESAI TANDATANGAN' AND status != 'DIMAKLUM'");
    $total_wait = $stmt_wait->fetchColumn();

    // Kira 'Selesai'
    $stmt_done = $conn->query("SELECT COUNT(*) FROM minit_surat WHERE status = 'SELESAI TANDATANGAN' OR status = 'DIMAKLUM'");
    $total_done = $stmt_done->fetchColumn();
} catch (PDOException $e) {
    $total_surat = $total_wait = $total_done = 0;
}
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

        body { font-family: 'Inter', sans-serif; background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('homeadmin.jpg'); background-repeat: no-repeat; background-size: cover; background-attachment: fixed; background-position: center center; margin: 0; padding: 0; color: var(--text-main); }
        .navbar { background: var(--primary-color); color: white; padding: 1.2rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .container { max-width: 1300px; margin: 40px auto; padding: 0 20px; }
        .admin-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .stat-card { background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .stat-info h4 { margin: 0; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; }
        .stat-info p { margin: 8px 0 0 0; font-size: 1.8rem; font-weight: 700; color: #0f172a; }
        .table-container { background: var(--card-bg); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid var(--border-color); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; color: var(--text-muted); padding: 16px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid var(--border-color); }
        td { padding: 16px; border-bottom: 1px solid var(--border-color); font-size: 0.95rem; color: #334155; }
        .status-badge { padding: 6px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .wait { background: #fee2e2; color: #991b1b; }
        .selesai-badge { background: #e0e7ff; color: #4338ca; }
        
        .btn-view { display: inline-block; padding: 6px 12px; background: #e0e7ff; color: #4338ca; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: 600; margin-bottom: 5px; }
        .btn-print { display: inline-block; padding: 6px 12px; background: #dcfce7; color: #166534; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: 600; }
        .btn-daftar { background: #059669; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: background 0.3s; }
        .btn-daftar:hover { background: #047857; }
        .header-actions { display: flex; align-items: center; gap: 20px; }
    </style>
</head>
<body>

<nav class="navbar">
    <h2><i class="fa-solid fa-folder-open"></i> Sistem Minit Digital</h2>
    <div class="header-actions">
        <span><?= htmlspecialchars($user_name) ?></span>
        <a href="daftar_surat.php" class="btn-daftar"><i class="fa-solid fa-plus"></i> Daftar Surat Masuk</a>
        <a href="logout.php" style="color:#f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Log Keluar</a>
    </div>
</nav>

<div class="container">
    <div class="admin-stats">
        <div class="stat-card"><div class="stat-info"><h4>Jumlah Surat</h4><p><?= $total_surat ?></p></div></div>
        <div class="stat-card"><div class="stat-info"><h4>Menunggu</h4><p><?= $total_wait ?></p></div></div>
        <div class="stat-card"><div class="stat-info"><h4>Selesai</h4><p><?= $total_done ?></p></div></div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Tarikh</th><th>No. Rujukan</th><th>Daripada</th><th>Status</th><th>Tindakan</th><th>Maklum Kepada</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Menggunakan fetch(PDO::FETCH_ASSOC) untuk PostgreSQL
                $res = $conn->query("SELECT * FROM minit_surat ORDER BY id DESC");
                while($row = $res->fetch(PDO::FETCH_ASSOC)) {
                    $status = trim($row['status'] ?? 'Menunggu');
                    $badge = ($status == 'SELESAI TANDATANGAN' || $status == 'DIMAKLUM') ? 'selesai-badge' : 'wait';
                    
                    echo "<tr>
                        <td>".date('d/m/Y', strtotime($row['tarikh_terima']))."</td>
                        <td>".htmlspecialchars($row['no_rujukan'] ?? '-')."</td>
                        <td>".htmlspecialchars($row['daripada'] ?? '-')."</td>
                        <td><span class='status-badge {$badge}'>{$status}</span></td>
                        <td>
                            <a href='view_surat.php?id={$row['id']}' class='btn-view'><i class='fa-solid fa-eye'></i> Lihat</a><br>
                            <a href='cetak_minit.php?id={$row['id']}' target='_blank' class='btn-print'><i class='fa-solid fa-print'></i> Cetak</a>
                        </td>
                        <td>" . (!empty($row['maklum_kepada']) ? "<span style='color:#0369a1; font-weight:bold;'>".htmlspecialchars($row['maklum_kepada'])."</span>" : "<a href='maklum.php?id={$row['id']}' style='color:#7c3aed; text-decoration:none;'><i class='fa-solid fa-paper-plane'></i> Maklum</a>") . "</td>
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
