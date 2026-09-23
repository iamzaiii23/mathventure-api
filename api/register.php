<?php
// api/register.php
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
$nama_lengkap = trim($input['nama_lengkap'] ?? '');
$role       = trim($input['role'] ?? '');

// Validasi input kosong
if (empty($identifier) || empty($password) || empty($nama_lengkap) || empty($role)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Semua kolom wajib diisi (identifier, password, nama_lengkap, role).'
    ]);
    exit;
}

// Validasi role yang diizinkan
if (!in_array($role, ['siswa', 'peneliti'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Role tidak valid. Pilih "siswa" atau "peneliti".'
    ]);
    exit;
}

try {
    // Cek apakah identifier sudah terdaftar
    $stmt = $pdo->prepare("SELECT id FROM users WHERE identifier = ?");
    $stmt->execute([$identifier]);
    if ($stmt->fetch()) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Identifier sudah terdaftar. Gunakan yang lain.'
        ]);
        exit;
    }

    // Enkripsi password menggunakan BCRYPT (default PHP password_hash)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Simpan ke database
    $insertStmt = $pdo->prepare("INSERT INTO users (identifier, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
    $insertStmt->execute([$identifier, $hashedPassword, $nama_lengkap, $role]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Registrasi berhasil. Silakan login.'
    ]);

} catch (\PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan pada server.',
        'debug' => $e->getMessage()
    ]);
}
?>