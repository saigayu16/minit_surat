<?php 
ob_start();
session_start();
include('db.php'); 

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'Admin Sistem';

// Kira statistik
$total_surat = $conn->query("SELECT COUNT(*) as total FROM minit_surat")->fetch_assoc()['total'];
$total_wait = $conn->query("SELECT COUNT(*) as total FROM minit_surat WHERE status != 'SELESAI TANDATANGAN'")->fetch_assoc()['total'];
$total_done = $conn->query("SELECT COUNT(*) as total FROM minit_surat WHERE status = 'SELESAI TANDATANGAN'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Sistem Minit Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... (CSS asal anda dikekalkan) ... */
        .status-badge { padding: 6px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; display: inline-block; }
        .done { background: #d1fae5; color: #065f46; }
        .selesai-tanda { background: #e0e7ff; color: #4338ca; } /* Warna baru untuk status tandatangan */
        .wait { background: #fee2e2; color: #991b1b; }
        .action-container { display: flex; flex-direction: column; gap: 5px; }
        .action-link { text-decoration: none; font-size: 0.85rem; font-weight: 600; padding: 5px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
    <div class="admin-stats">
        <div class="stat-card"><h4>Jumlah</h4><p><?= $total_surat ?></p></div>
        <div class="stat-card"><h4>Menunggu</h4><p><?= $total_wait ?></p></div>
        <div class="stat-card"><h4>Selesai</h4><p><?= $total_done ?></p></div>
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
                    $status = $row['status'] ?? 'Menunggu';
                    // Logik Badge
                    $badge = ($status == 'SELESAI TANDATANGAN') ? 'selesai-tanda' : (($status == 'DISAHKAN') ? 'done' : 'wait');
                    
                    echo "<tr>
                        <td>".date('d/m/Y', strtotime($row['tarikh_terima']))."</td>
                        <td>".htmlspecialchars($row['no_rujukan'])."</td>
                        <td>".htmlspecialchars($row['daripada'])."</td>
                        <td><span class='status-badge {$badge}'>{$status}</span></td>
                        
                        <td>
                            <div class='action-container'>
                                <a href='view_surat.php?id={$row['id']}' class='action-link' style='color:#2563eb;'>👁 Lihat</a>
                                ".($status == 'SELESAI TANDATANGAN' ? "<a href='cetak_minit.php?id={$row['id']}' target='_blank' class='action-link' style='color:#16a34a;'>🖨 Cetak</a>" : "")."
                            </div>
                        </td>
                        
                        <td>" . (!empty($row['maklum_kepada']) ? htmlspecialchars($row['maklum_kepada']) : "<a href='maklum.php?id={$row['id']}'>Maklum</a>") . "</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
