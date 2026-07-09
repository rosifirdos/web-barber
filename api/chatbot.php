<?php
/**
 * IF Barber — Chatbot API Endpoint (Hybrid)
 * Menggunakan logika hardcoded sebagai fallback, dan Gemini API hanya untuk memperhalus bahasa.
 */
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Rate limiting: maks 10 request per menit per session
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['chatbot_rate'])) {
    $_SESSION['chatbot_rate'] = ['count' => 0, 'reset' => time()];
}
if (time() - $_SESSION['chatbot_rate']['reset'] > 60) {
    $_SESSION['chatbot_rate'] = ['count' => 0, 'reset' => time()];
}
$_SESSION['chatbot_rate']['count']++;
if ($_SESSION['chatbot_rate']['count'] > 10) {
    http_response_code(429);
    echo json_encode(['status' => 'error', 'message' => 'Terlalu banyak permintaan. Coba lagi dalam 1 menit.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim(strip_tags($input['message'] ?? ''));
$userMessageLower = strtolower($userMessage);

if (empty($userMessage)) {
    echo json_encode(['status' => 'error', 'message' => 'Pesan tidak boleh kosong.']);
    exit;
}

// Batasi panjang pesan
if (mb_strlen($userMessage) > 500) {
    echo json_encode(['status' => 'error', 'message' => 'Pesan terlalu panjang. Maksimal 500 karakter.']);
    exit;
}

// 1. Tentukan Jawaban Mentah (Hardcoded Draft) berdasarkan kata kunci
$draftAnswer = "";

if (strpos($userMessageLower, 'jam') !== false || strpos($userMessageLower, 'buka') !== false || strpos($userMessageLower, 'tutup') !== false || strpos($userMessageLower, 'jadwal') !== false) {
    $draftAnswer = "IF Barber buka setiap hari Senin sampai Sabtu, mulai pukul " . JAM_BUKA . " hingga " . JAM_TUTUP . " WIB. Hari Minggu kami tutup.";
} elseif (strpos($userMessageLower, 'harga') !== false || strpos($userMessageLower, 'layanan') !== false || strpos($userMessageLower, 'biaya') !== false || strpos($userMessageLower, 'tarif') !== false || strpos($userMessageLower, 'potong') !== false) {
    $draftAnswer = "Kami menyediakan berbagai layanan: Haircut Premium (Rp 50.000), Haircut & Beard Trim (Rp 75.000), Hair Coloring (Rp 150.000), dan Kid's Haircut (Rp 35.000). Alat steril dan ruang full AC.";
} elseif (strpos($userMessageLower, 'lokasi') !== false || strpos($userMessageLower, 'alamat') !== false || strpos($userMessageLower, 'dimana') !== false) {
    $draftAnswer = "Lokasi IF Barber berada di Jl. Contoh Alamat No. 123. Anda bisa langsung datang atau booking online terlebih dahulu.";
} elseif (strpos($userMessageLower, 'booking') !== false || strpos($userMessageLower, 'pesan') !== false || strpos($userMessageLower, 'antre') !== false || strpos($userMessageLower, 'daftar') !== false) {
    $draftAnswer = "Untuk menghindari antrean, Anda sangat disarankan untuk melakukan booking jadwal secara online melalui website ini. Silakan klik tombol 'Book Now' di bagian atas halaman.";
} elseif (strpos($userMessageLower, 'barber') !== false || strpos($userMessageLower, 'kapster') !== false || strpos($userMessageLower, 'tukang cukur') !== false) {
    $draftAnswer = "Kami memiliki beberapa barber profesional: Arief (Spesialis Classic Cut), Bayu (Spesialis Fade), Dimas (Hair Tattoo), dan Reza (Hair Coloring).";
} else {
    $draftAnswer = "Halo! Saya adalah asisten virtual IF Barber. Anda bisa bertanya kepada saya seputar jam buka, daftar harga layanan, lokasi, atau cara booking online. Ada yang bisa saya bantu?";
}

// Jika API Key tidak ada, langsung kembalikan jawaban mentah (tanpa error)
if (empty(GEMINI_API_KEY)) {
    echo json_encode([
        'status' => 'success',
        'reply' => $draftAnswer
    ]);
    exit;
}

// 2. Gunakan Gemini API hanya untuk memperhalus (Paraphrasing) agar lebih natural
$systemPrompt = "
Anda adalah asisten IF Barber. Tugas Anda HANYA SATU: memperhalus kalimat jawaban mentah (draft) agar terdengar lebih natural, ramah, dan profesional layaknya manusia saat membalas pesan pelanggan. 
ATURAN MUTLAK:
- JANGAN MENGUBAH FAKTA dari jawaban mentah (jam, harga, nama).
- JANGAN MENAMBAH INFORMASI yang tidak ada di jawaban mentah.
- Cukup tulis ulang kalimatnya agar lebih luwes dan komunikatif. Tambahkan sapaan jika perlu. Boleh pakai sedikit emoji.
";

$userPrompt = "Pesan pelanggan: \"{$userMessage}\"\nJawaban mentah yang harus Anda perhalus: \"{$draftAnswer}\"";

$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent?key=' . GEMINI_API_KEY;

$data = [
    'system_instruction' => [
        'parts' => [['text' => $systemPrompt]]
    ],
    'contents' => [
        ['role' => 'user', 'parts' => [['text' => $userPrompt]]]
    ],
    'generationConfig' => [
        'temperature' => 0.5,
        'maxOutputTokens' => 200,
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
// Hanya disable SSL di environment development (localhost)
if (in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'])) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
}
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set timeout maksimal 10 detik agar tidak hang jika API lambat

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// 3. Evaluasi respon Gemini. Jika terjadi error koneksi atau Quota Exceeded (429), gunakan $draftAnswer!
if ($error || $httpCode !== 200) {
    echo json_encode([
        'status' => 'success',
        'reply' => $draftAnswer
    ]);
    exit;
}

$responseData = json_decode($response, true);
$reply = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';

// Jika entah kenapa balasan Gemini kosong, tetap gunakan $draftAnswer
if (empty(trim($reply))) {
    $reply = $draftAnswer;
}

echo json_encode([
    'status' => 'success',
    'reply' => trim($reply)
]);
