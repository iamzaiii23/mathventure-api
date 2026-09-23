<?php
// api/login.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Pastikan method request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan. Gunakan POST.'
    ]);
    exit;
}

// Ambil data JSON dari body request
$input = json_decode(file_get_contents('php://input'), true);

$identifier = trim($input['identifier'] ?? '');
$password   = trim($input['password'] ?? '');

// Validasi input kosong
if (empty($identifier) || empty($password)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Identifier dan password wajib diisi.'
    ]);
    exit;
}

try {
    // Cari user berdasarkan identifier
    $stmt = $pdo->prepare("SELECT * FROM users WHERE identifier = ? AND is_active = 1 AND deleted_at IS NULL");
    $stmt->execute([$identifier]);
    $user = $stmt->fetch();

    // Verifikasi user dan password yang dienkripsi
    if ($user && password_verify($password, $user['password'])) {
        // Login sukses, kirim data profil ringkas (tanpa password)
        echo json_encode([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'data' => [
                'id' => $user['id'],
                'identifier' => $user['identifier'],
                'nama_lengkap' => $user['nama_lengkap'],
                'role' => $user['role']
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Identifier atau password salah.'
        ]);
    }

} catch (\PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan pada server.',
        'debug' => $e->getMessage()
    ]);
}
?>