<?php
// api/submit_answer.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan. Gunakan POST.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$session_id    = trim($input['session_id'] ?? '');
$puzzle_id     = trim($input['puzzle_id'] ?? '');
$input_jawaban = trim($input['input_jawaban'] ?? '');

if (empty($session_id) || empty($puzzle_id) || empty($input_jawaban)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Parameter session_id, puzzle_id, dan input_jawaban wajib diisi.'
    ]);
    exit;
}

try {
    // Ambil kunci jawaban dari database berdasarkan puzzle_id
    $stmt = $pdo->prepare("SELECT kunci_jawaban, poin_xp FROM puzzles WHERE id = ?");
    $stmt->execute([$puzzle_id]);
    $puzzle = $stmt->fetch();

    if (!$puzzle) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Puzzle tidak ditemukan.'
        ]);
        exit;
    }

    // Validasi jawaban (case-insensitive atau trim yang bersih)
    $is_correct = (strcasecmp(trim($puzzle['kunci_jawaban']), $input_jawaban) === 0) ? 1 : 0;

    // Simpan log aktivitas ke tabel activity_logs
    $logStmt = $pdo->prepare("INSERT INTO activity_logs (session_id, puzzle_id, input_jawaban, is_correct) VALUES (?, ?, ?, ?)");
    $logStmt->execute([$session_id, $puzzle_id, $input_jawaban, $is_correct]);

    echo json_encode([
        'status' => 'success',
        'is_correct' => (bool)$is_correct,
        'message' => $is_correct ? 'Jawaban benar! Hebat.' : 'Jawaban masih salah. Coba lagi.'
    ]);

} catch (\PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan pada server.',
        'debug' => $e->getMessage()
    ]);
}
?>