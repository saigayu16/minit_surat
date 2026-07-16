<?php
session_start();
include('db.php'); // Pastikan db.php menggunakan connection PDO

// Memanggil pustaka FPDF & FPDI
require_once('fpdf/fpdf.php');
require_once('fpdf/src/autoload.php');

use Setasign\Fpdi\Fpdi;

if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_POST['surat_id']) ? intval($_POST['surat_id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
if ($id === 0) { 
    echo "<script>alert('Ralat: ID dokumen tidak sah.'); window.location='homedirector.php';</script>";
    exit;
}

$fileName = '';
if (isset($_POST['image']) && !empty($_POST['image']) && $_POST['image'] !== 'undefined') {
    $img = str_replace('data:image/png;base64,', '', $_POST['image']);
    $fileName = 'ttd_' . $id . '_' . time() . '.png';
    $path_ttd = 'uploads/' . $fileName;
    file_put_contents($path_ttd, base64_decode($img));
}

// 1. Ambil maklumat fail surat asal (Guna PDO)
$stmt = $conn->prepare("SELECT fail_surat, no_rujukan FROM minit_surat WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($data && !empty($data['fail_surat'])) {
    $nama_fail_asal = $data['fail_surat'];
    $no_rujukan = $data['no_rujukan'];
    $path_fail_asal = 'uploads/' . $nama_fail_asal;
    
    if (file_exists($path_fail_asal)) {
        
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($path_fail_asal);
        
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
            
            if ($pageNo === $pageCount) {
                $pdf->SetDrawColor(234, 179, 8);
                $pdf->SetFillColor(254, 240, 138);
                $x_box = $size['width'] - 95;
                $y_box = $size['height'] - 65;
                
                $pdf->Rect($x_box, $y_box, 85, 55, 'DF');
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetTextColor(22, 163, 74);
                $pdf->SetXY($x_box + 22, $y_box + 4);
                $pdf->Cell(40, 5, "DILULUSKAN DIGITAL", 0, 1, 'C');
                
                if (!empty($fileName) && file_exists('uploads/' . $fileName)) {
                    $pdf->Image('uploads/' . $fileName, $x_box + 22, $y_box + 10, 40, 18);
                }
                
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->Line($x_box + 10, $y_box + 30, $x_box + 75, $y_box + 30);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetXY($x_box, $y_box + 32);
                $pdf->Cell(85, 4, "PENGARAH", 0, 1, 'C');
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetXY($x_box, $y_box + 37);
                $pdf->Cell(85, 4, "Kolej Komuniti Kepala Batas", 0, 1, 'C');
                $pdf->SetTextColor(185, 28, 28);
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($x_box, $y_box + 44);
                $pdf->Cell(85, 4, "TARIKH: " . date('d/m/Y'), 0, 1, 'C');
            }
        }
        
        $path_output_bercop = 'uploads/BERCOP_' . $nama_fail_asal;
        $pdf->Output('F', $path_output_bercop);
        
        // 3. HANTAR KE GOOGLE DRIVE
        $nama_baru_drive = 'MINIT_DISAHKAN_' . str_replace('/', '-', $no_rujukan) . '_' . $id . '.pdf';
        $webAppUrl = 'https://script.google.com/macros/s/AKfycbxFL9V5-bXoDKlinxm-n5KT1IeTmh0Uposh4vgTdKbKQX-9bwkQe5Vs8zZJtRIzvzKy/exec';
        $google_folder_id = '1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1';

        try {
            $file_content = base64_encode(file_get_contents($path_output_bercop));
            $payload = json_encode(["image" => $file_content, "fileName" => $nama_baru_drive, "folderId" => $google_folder_id]);

            $ch = curl_init($webAppUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_exec($ch);
            curl_close($ch);
            
            if (file_exists($path_output_bercop)) { unlink($path_output_bercop); }

            // 4. Update Database (Guna PDO)
            $update_sql = "UPDATE minit_surat SET status = 'Sudah Disahkan', tarikh_disahkan = CURRENT_DATE";
            if (!empty($fileName)) { $update_sql .= ", tandatangan_fail = ?"; }
            $update_sql .= " WHERE id = ?";
            
            $stmt_update = $conn->prepare($update_sql);
            if (!empty($fileName)) {
                $stmt_update->execute([$fileName, $id]);
            } else {
                $stmt_update->execute([$id]);
            }

            echo "<script>alert('Dokumen berjaya disahkan!'); window.location='homedirector.php';</script>";
            exit;
            
        } catch (Exception $e) {
            $conn->prepare("UPDATE minit_surat SET status = 'Sudah Disahkan', tarikh_disahkan = CURRENT_DATE WHERE id = ?")->execute([$id]);
            echo "<script>alert('Gagal hantar ke Drive, tapi sistem lokal sudah kemaskini.'); window.location='homedirector.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Ralat: Fail asal tidak dijumpai.'); window.location='homedirector.php';</script>";
    }
} else {
    echo "<script>alert('Ralat: Rekod tidak wujud.'); window.location='homedirector.php';</script>";
}
?>
