<?php
// api/admin_dashboard.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
session_start();

// Otorisasi: Cegah akses jika belum login atau bukan peneliti
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'peneliti') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Akses ditolak. Halaman ini khusus Dasbor Peneliti.'
    ]);
    exit;
}

try {
    // 1. Hitung total siswa aktif
    $stmtSiswa = $pdo->query("SELECT COUNT(id) as total FROM users WHERE role = 'siswa' AND is_active = 1 AND deleted_at IS NULL");
    $totalSiswa = $stmtSiswa->fetch()['total'] ?? 0;

    // 2. Hitung rata-rata skor dari sesi permainan yang sudah selesai
    $stmtSkor = $pdo->query("SELECT AVG(total_skor) as rata_rata FROM game_sessions WHERE waktu_selesai IS NOT NULL");
    $rataRataSkor = $stmtSkor->fetch()['rata_rata'] ?? 0;

    // 3. Hitung total sesi yang berhasil diselesaikan
    $stmtSesi = $pdo->query("SELECT COUNT(session_id) as total FROM game_sessions WHERE waktu_selesai IS NOT NULL");
    $totalSesi = $stmtSesi->fetch()['total'] ?? 0;

    echo json_encode([
        'status' => 'success',
        'message' => 'Data Dasbor Peneliti berhasil dimuat.',
        'data' => [
            'total_siswa_aktif' => (int)$totalSiswa,
            'total_sesi_selesai' => (int)$totalSesi,
            'rata_rata_skor' => round($rataRataSkor, 2)
        ]
    ]);

} catch (\PDOException $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Gagal memuat data metrik.', 
        'debug' => $e->getMessage()
    ]);
}
?>