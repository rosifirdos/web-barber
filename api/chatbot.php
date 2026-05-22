<?php
/**
 * IF Barber — Gemini Chatbot API Endpoint
 */
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty(GEMINI_API_KEY)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'API Key belum dikonfigurasi. Admin perlu menambahkan GEMINI_API_KEY di config.php.'
    ]);
    exit;
}

if (empty($userMessage)) {
    echo json_encode(['status' => 'error', 'message' => 'Pesan tidak boleh kosong.']);
    exit;
}

// System Prompt: Konteks untuk Chatbot IF Barber
$systemPrompt = "
Anda adalah asisten virtual resmi untuk 'IF Barber'. Jawablah pertanyaan pelanggan dengan bahasa Indonesia yang ramah, profesional, sopan, dan cukup singkat (maksimal 2 paragraf pendek). Gunakan format teks biasa, tanpa markdown kompleks, tapi boleh pakai emoji secukupnya.
Informasi IF Barber:
- Alamat: Jl. Contoh Alamat No. 123, Kota Anda.
- Jam Buka: Senin - Sabtu (09:00 - 21:00 WIB). Minggu Tutup.
- Layanan yang tersedia:
  1. Haircut Premium (Rp 50.000) - Cuci, potong, pijat, styling.
  2. Haircut & Beard Trim (Rp 75.000) - Potong rambut dan cukur/rapihkan brewok.
  3. Hair Coloring (Rp 150.000) - Pewarnaan rambut pria.
  4. Kid's Haircut (Rp 35.000) - Potong rambut anak (<12 tahun).
- Barber kami: Arief (Spesialis Classic Cut), Bayu (Spesialis Fade), Dimas (Hair Tattoo), Reza (Hair Coloring).
- Cara booking online: Pelanggan dapat melakukan booking jadwal melalui website ini dengan menekan tombol 'Book Now' di menu atas.
- Keunggulan: Barbershop premium, alat steril, ruang full AC, free WiFi.

Aturan Tambahan:
- Jangan memberikan saran medis kulit kepala.
- Jika ditanya hal di luar urusan barbershop atau pangkas rambut, tolak dengan halus dan arahkan kembali ke topik layanan IF Barber.
- Selalu dorong pengguna untuk melakukan booking online.
";

$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent?key=' . GEMINI_API_KEY;

$data = [
    'system_instruction' => [
        'parts' => [
            ['text' => $systemPrompt]
        ]
    ],
    'contents' => [
        [
            'role' => 'user',
            'parts' => [
                ['text' => $userMessage]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 300,
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
// CURLOPT_SSL_VERIFYPEER set false agar jalan mulus di localhost XAMPP tanpa sertifikat SSL valid
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi ke server gagal: ' . $error]);
    exit;
}

$responseData = json_decode($response, true);

if ($httpCode !== 200) {
    $apiErrorMsg = $responseData['error']['message'] ?? 'Unknown error';
    echo json_encode(['status' => 'error', 'message' => 'Gemini API Error: ' . $apiErrorMsg]);
    exit;
}

$reply = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya tidak dapat memproses permintaan tersebut.';

echo json_encode([
    'status' => 'success',
    'reply' => trim($reply)
]);
