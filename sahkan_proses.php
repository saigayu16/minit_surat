<?php
session_start();
include('db.php'); // Menggunakan fail db.php yang telah dibetulkan namanya

// Memanggil pustaka FPDF & FPDI yang telah diekstrak ke folder fpdf/
require_once('fpdf/fpdf.php');
require_once('fpdf/src/autoload.php');

use Setasign\Fpdi\Fpdi;

// Semak sesi pengguna terlebih dahulu
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Menyelaraskan penerimaan ID sama ada dari input 'surat_id' atau 'id'
$id = isset($_POST['surat_id']) ? intval($_POST['surat_id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
if ($id === 0) { 
    echo "<script>alert('Ralat: ID dokumen tidak sah.'); window.location='homedirector.php';</script>";
    exit;
}

// Memproses lakaran imej tandatangan jika dihantar melalui POST
$fileName = '';
if (isset($_POST['image']) && !empty($_POST['image']) && $_POST['image'] !== 'undefined') {
    $img = str_replace('data:image/png;base64,', '', $_POST['image']);
    $fileName = 'ttd_' . $id . '_' . time() . '.png';
    $path_ttd = 'uploads/' . $fileName;
    file_put_contents($path_ttd, base64_decode($img));
}

// 1. Ambil maklumat fail surat asal daripada table minit_surat
$query = $conn->query("SELECT fail_surat, no_rujukan FROM minit_surat WHERE id = $id");
$data = $query->fetch_assoc();

if ($data && !empty($data['fail_surat'])) {
    $nama_fail_asal = $data['fail_surat'];
    $no_rujukan = $data['no_rujukan'];
    $path_fail_asal = 'uploads/' . $nama_fail_asal;
    
    // Periksa jika fail PDF fizikal benar-benar wujud di folder uploads/
    if (file_exists($path_fail_asal)) {
        
        // 2. MENGGUNAKAN FPDI UNTUK MEMBUKA FAIL ASAL & MENAMPAL COP + TANDATANGAN
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($path_fail_asal);
        
        // Salin setiap helaian mukasurat fail rujukan asal
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
            
            // Tampal kotak cop kuning hanya pada helaian Muka Surat Terakhir dokumen tersebut
            if ($pageNo === $pageCount) {
                // Konfigurasi warna border (Kuning/Emas) & warna latar (Kuning Lembut)
                $pdf->SetDrawColor(234, 179, 8);
                $pdf->SetFillColor(254, 240, 138);
                
                // Kedudukan kotak cop (Kanan bawah mukasurat)
                $x_box = $size['width'] - 95;
                $y_box = $size['height'] - 65;
                
                // Lukis Kotak Cop
                $pdf->Rect($x_box, $y_box, 85, 55, 'DF');
                
                // Tulisan teks pengesahan digital
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetTextColor(22, 163, 74); // Warna Hijau
                $pdf->SetXY($x_box + 22, $y_box + 4);
                $pdf->Cell(40, 5, "DILULUSKAN DIGITAL", 0, 1, 'C');
                
                // Letakkan imej lakaran tandatangan pengarah jika ada
                if (!empty($fileName) && file_exists('uploads/' . $fileName)) {
                    $pdf->Image('uploads/' . $fileName, $x_box + 22, $y_box + 10, 40, 18);
                }
                
                // Garis pemisah jawatan
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->Line($x_box + 10, $y_box + 30, $x_box + 75, $y_box + 30);
                
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetXY($x_box, $y_box + 32);
                $pdf->Cell(85, 4, "PENGARAH", 0, 1, 'C');
                
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetXY($x_box, $y_box + 37);
                $pdf->Cell(85, 4, "Kolej Komuniti Kepala Batas", 0, 1, 'C');
                
                // Cetakan tarikh kelulusan
                $pdf->SetTextColor(185, 28, 28); // Warna Merah
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetXY($x_box, $y_box + 44);
                $pdf->Cell(85, 4, "TARIKH: " . date('d/m/Y'), 0, 1, 'C');
            }
        }
        
        // Simpan fail bercop baharu ke folder sementara hos backend
        $path_output_bercop = 'uploads/BERCOP_' . $nama_fail_asal;
        $pdf->Output('F', $path_output_bercop);
        
       // 3. MENGHANTAR FAIL ASAL YANG SIAP DICOP KE GOOGLE DRIVE VIA GAS (URL TERBAHARU ANDA)
$nama_baru_drive = 'MINIT_DISAHKAN_' . str_replace('/', '-', $no_rujukan) . '_' . $id . '.pdf';

// 🌟 TAMPAL URL NEW DEPLOYMENT ANDA YANG BAHARU DI SINI
$webAppUrl = 'https://script.google.com/macros/s/AKfycbxFL9V5-bXoDKlinxm-n5KT1IeTmh0Uposh4vgTdKbKQX-9bwkQe5Vs8zZJtRIzvzKy/exec';

// 🔒 ID Folder ini kekal sama, tidak perlu diubah langsung!
$google_folder_id = '1jXktGUFE2kZ32_LSk9DuybBsdXel6dL1';

        try {
            $file_content = base64_encode(file_get_contents($path_output_bercop));

            $payload = json_encode([
                "image" => $file_content, 
                "fileName" => $nama_baru_drive,
                "folderId" => $google_folder_id 
            ]);

            $ch = curl_init($webAppUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            curl_close($ch);
            
            // Bersihkan fail output bercop sementara untuk jimat storan server lokal XAMPP
            if (file_exists($path_output_bercop)) { unlink($path_output_bercop); }

            // 4. Kemas kini rekod status dalam database tempatan selepas cubaan hantar selesai
            $sql = "UPDATE minit_surat SET status = 'Sudah Disahkan', tarikh_disahkan = CURDATE()";
            if (!empty($fileName)) { $sql .= ", tandatangan_fail = '$fileName'"; }
            $sql .= " WHERE id = $id";
            $conn->query($sql);

            echo "<script>alert('Dokumen berjaya disahkan & salinan dihantar ke Google Drive Rasmi Kolej!'); window.location='homedirector.php';</script>";
            exit;
            
        } catch (Exception $e) {
            file_put_contents('ralat_google.txt', "Catch Error: " . $e->getMessage());
            
            // Tetap kemas kini DB tempatan sebagai sandaran walaupun ralat rangkaian Drive berlaku
            $conn->query("UPDATE minit_surat SET status = 'Sudah Disahkan', tarikh_disahkan = CURDATE() WHERE id = $id");
            
            echo "<script>alert('Dokumen disahkan di sistem lokal, tetapi gagal dihantar ke Drive.'); window.location='homedirector.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Ralat: Fail fizikal asal tidak dijumpai di server XAMPP.'); window.location='homedirector.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('Ralat: Rekod data dokumen tidak wujud.'); window.location='homedirector.php';</script>";
    exit;
}
?>