<?php
// api/get_puzzles.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Endpoint ini menggunakan metode GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan. Gunakan GET.'
    ]);
    exit;
}

try {
    // Ambil semua daftar puzzle dari database
    $stmt = $pdo->prepare("SELECT id, ruangan_id, pertanyaan, poin_xp FROM puzzles ORDER BY ruangan_id ASC");
    $stmt->execute();
    $puzzles = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'message' => 'Daftar puzzle berhasil dimuat.',
        'data' => $puzzles
    ]);

} catch (\PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan pada server.',
        'debug' => $e->getMessage()
    ]);
}
?>