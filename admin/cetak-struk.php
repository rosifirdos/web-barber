<?php
/**
 * IF Barber — Cetak Struk (PDF)
 * Generate PDF Invoice/Struk untuk pemesanan yang telah selesai
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/fpdf/fpdf.php';

// Verifikasi login admin
if (!isAdminLoggedIn()) {
    redirect(BASE_URL . '/admin/login.php');
}

// Ambil ID booking dari parameter GET
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("ID Booking tidak valid.");
}

// Ambil data booking beserta detail layanan dan barber
$query = "SELECT b.*, l.nama as nama_layanan, l.harga as harga_layanan, bar.nama as nama_barber 
          FROM booking b 
          JOIN layanan l ON b.layanan_id = l.id 
          JOIN barber bar ON b.barber_id = bar.id 
          WHERE b.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Data booking tidak ditemukan.");
}

$booking = $result->fetch_assoc();
$stmt->close();

// Buat class turunan FPDF untuk custom Header dan Footer
class PDF extends FPDF {
    // Page header
    function Header() {
        // Logo atau Nama Brand
        $this->SetFont('Arial', 'B', 24);
        $this->SetTextColor(200, 169, 110); // Gold accent color
        $this->Cell(0, 15, 'IF BARBER', 0, 1, 'C');
        
        // Alamat / Tagline
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, 'Premium Grooming Experience', 0, 1, 'C');
        $this->Cell(0, 5, 'Jl. Contoh Alamat No. 123, Kota Anda', 0, 1, 'C');
        
        // Garis pemisah
        $this->SetDrawColor(200, 169, 110);
        $this->SetLineWidth(0.5);
        $this->Line(10, 38, 200, 38);
        $this->Ln(15);
    }

    // Page footer
    function Footer() {
        // Posisi 1.5 cm dari bawah
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Terima kasih telah mempercayakan gaya rambut Anda kepada IF Barber.', 0, 0, 'C');
    }
}

// Inisiasi dokumen PDF
$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();

// Info Struk
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, 'INVOICE / STRUK LAYANAN', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 8, 'No. Order', 0, 0);
$pdf->Cell(5, 8, ':', 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, '#' . str_pad($booking['id'], 4, '0', STR_PAD_LEFT), 0, 1);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 8, 'Tanggal Transaksi', 0, 0);
$pdf->Cell(5, 8, ':', 0, 0);
$pdf->Cell(0, 8, date('d/m/Y H:i'), 0, 1);

$pdf->Cell(40, 8, 'Status', 0, 0);
$pdf->Cell(5, 8, ':', 0, 0);
$pdf->SetFont('Arial', 'B', 12);
if ($booking['status'] === 'Selesai') {
    $pdf->SetTextColor(40, 167, 69); // Hijau
} else {
    $pdf->SetTextColor(220, 53, 69); // Merah jika belum selesai
}
$pdf->Cell(0, 8, strtoupper($booking['status']), 0, 1);
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(10);

// Info Pelanggan
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(0, 10, ' DETAIL PELANGGAN', 0, 1, 'L', true);
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 8, 'Nama Pelanggan', 0, 0);
$pdf->Cell(5, 8, ':', 0, 0);
$pdf->Cell(0, 8, $booking['nama_pelanggan'], 0, 1);

$pdf->Cell(40, 8, 'Nomor HP', 0, 0);
$pdf->Cell(5, 8, ':', 0, 0);
$pdf->Cell(0, 8, $booking['no_hp'], 0, 1);
$pdf->Ln(10);

// Detail Layanan (Tabel)
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, ' DETAIL PEMESANAN', 0, 1, 'L', true);
$pdf->Ln(2);

// Header Tabel
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(200, 169, 110);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(60, 10, 'Layanan', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Barber', 1, 0, 'C', true);
$pdf->Cell(45, 10, 'Waktu', 1, 0, 'C', true);
$pdf->Cell(45, 10, 'Harga', 1, 1, 'C', true);

// Isi Tabel
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(60, 10, $booking['nama_layanan'], 1, 0, 'C');
$pdf->Cell(40, 10, $booking['nama_barber'], 1, 0, 'C');
$pdf->Cell(45, 10, formatTanggal($booking['tanggal']) . ' ' . formatJam($booking['jam']), 1, 0, 'C');
$pdf->Cell(45, 10, formatRupiah($booking['total_harga']), 1, 1, 'R');

// Total
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(145, 10, 'TOTAL PEMBAYARAN', 1, 0, 'R');
$pdf->SetTextColor(200, 169, 110); // Gold
$pdf->Cell(45, 10, formatRupiah($booking['total_harga']), 1, 1, 'R');

$pdf->Ln(20);

// Tanda Tangan
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(130, 8, '', 0, 0);
$pdf->Cell(60, 8, 'Hormat Kami,', 0, 1, 'C');
$pdf->Ln(20);
$pdf->Cell(130, 8, '', 0, 0);
$pdf->Cell(60, 8, '( IF Barber Admin )', 0, 1, 'C');

// Output PDF ke browser
$pdf->Output('I', 'Struk_IF_Barber_' . str_pad($booking['id'], 4, '0', STR_PAD_LEFT) . '.pdf');
