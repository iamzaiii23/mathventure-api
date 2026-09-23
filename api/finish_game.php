<?php
// api/finish_game.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Gunakan metode POST.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$session_id = $input['session_id'] ?? '';

if (empty($session_id)) {
    echo json_encode(['status' => 'error', 'message' => 'session_id wajib diisi.']);
    exit;
}

try {
    // Kalkulasi total poin dari jawaban yang benar
    $calcStmt = $pdo->prepare("
        SELECT SUM(p.poin_xp) as total_skor 
        FROM activity_logs a 
        JOIN puzzles p ON a.puzzle_id = p.id 
        WHERE a.session_id = ? AND a.is_correct = 1
    ");
    $calcStmt->execute([$session_id]);
    $result = $calcStmt->fetch();
    $total_skor = $result['total_skor'] ?? 0;

    // Simpan skor akhir ke tabel game_sessions
    $updateStmt = $pdo->prepare("UPDATE game_sessions SET total_skor = ?, waktu_selesai = CURRENT_TIMESTAMP WHERE session_id = ?");
    $updateStmt->execute([$total_skor, $session_id]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Escape Room selesai!',
        'data' => [
            'session_id' => $session_id,
            'total_skor' => (int)$total_skor
        ]
    ]);
} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengakhiri sesi.', 'debug' => $e->getMessage()]);
}
?>