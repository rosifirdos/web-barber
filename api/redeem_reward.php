<?php
/**
 * IF Barber — API: Redeem Reward
 * POST { reward_id: int }
 * Response: JSON { status, message, kode_voucher }
 */

header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Hanya menerima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

// Harus login sebagai member
if (!isMemberLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu.']);
    exit;
}

$memberId = $_SESSION['member_id'];

// Ambil reward_id dari body
$input = json_decode(file_get_contents('php://input'), true);
$rewardId = isset($input['reward_id']) ? (int)$input['reward_id'] : 0;

if ($rewardId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Reward ID tidak valid.']);
    exit;
}

// Proses redeem
$result = redeemPoin($conn, $memberId, $rewardId);

if ($result['success']) {
    // Ambil saldo terbaru
    $poinInfo = getMemberPoin($conn, $memberId);
    echo json_encode([
        'status' => 'success',
        'message' => $result['message'],
        'kode_voucher' => $result['kode_voucher'],
        'sisa_poin' => $poinInfo['total_poin'],
        'tier' => $poinInfo['tier']
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => $result['message']
    ]);
}
