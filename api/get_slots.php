<?php
/**
 * IF Barber — API: Get Booked Slots
 * Mengembalikan slot waktu yang sudah terisi untuk barber & tanggal tertentu
 *
 * GET ?barber_id=1&tanggal=2026-05-22
 * Response: JSON { "booked": ["09:00", "10:30", ...] }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$barberId = isset($_GET['barber_id']) ? (int)$_GET['barber_id'] : 0;
$tanggal = isset($_GET['tanggal']) ? sanitize($_GET['tanggal']) : '';

// Validate
if ($barberId <= 0 || empty($tanggal)) {
    echo json_encode(['error' => 'Parameter barber_id dan tanggal wajib diisi.', 'booked' => []]);
    exit;
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    echo json_encode(['error' => 'Format tanggal tidak valid.', 'booked' => []]);
    exit;
}

// Check if holiday
if (isHariLibur($tanggal)) {
    echo json_encode(['error' => 'Barbershop tutup pada hari tersebut.', 'booked' => [], 'closed' => true]);
    exit;
}

// Get booked slots
$bookedSlots = getBookedSlots($conn, $barberId, $tanggal);

echo json_encode([
    'booked' => $bookedSlots,
    'tanggal' => $tanggal,
    'barber_id' => $barberId,
    'closed' => false
]);
