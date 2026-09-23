<?php
// api/start_session.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Gunakan metode POST.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'] ?? $input['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'User ID tidak ditemukan.']);
    exit;
}

try {
    // Generate ID Sesi
    $session_id = 'SESS-' . time() . '-' . rand(100, 999);
    
    $stmt = $pdo->prepare("INSERT INTO game_sessions (session_id, user_id) VALUES (?, ?)");
    $stmt->execute([$session_id, $user_id]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Sesi permainan Escape Room dimulai.',
        'data' => ['session_id' => $session_id]
    ]);
} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal memulai sesi.', 'debug' => $e->getMessage()]);
}
?>