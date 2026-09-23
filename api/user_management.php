<?php
// api/user_management.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
session_start();

// Otorisasi khusus Peneliti
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'peneliti') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Khusus Peneliti.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Gunakan metode POST.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    if ($action === 'add') {
        // 1. Tambah Siswa Baru
        $identifier = trim($input['identifier'] ?? '');
        $password = trim($input['password'] ?? '');
        $nama_lengkap = trim($input['nama_lengkap'] ?? '');
        $role = 'siswa'; // Paksa role siswa agar peneliti tidak bisa membuat admin baru

        if (empty($identifier) || empty($password) || empty($nama_lengkap)) {
            echo json_encode(['status' => 'error', 'message' => 'identifier, password, dan nama_lengkap wajib diisi.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE identifier = ?");
        $stmt->execute([$identifier]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Identifier sudah digunakan.']);
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (identifier, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
        $insert->execute([$identifier, $hashedPassword, $nama_lengkap, $role]);

        echo json_encode(['status' => 'success', 'message' => 'Data siswa berhasil ditambahkan.']);

    } elseif ($action === 'reset_password') {
        // 2. Reset Password Siswa
        $target_user_id = $input['user_id'] ?? '';
        $new_password = trim($input['new_password'] ?? '');

        if (empty($target_user_id) || empty($new_password)) {
            echo json_encode(['status' => 'error', 'message' => 'user_id dan new_password wajib diisi.']);
            exit;
        }

        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
        // Pastikan hanya bisa mereset akun siswa
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'siswa'");
        $update->execute([$hashedPassword, $target_user_id]);

        if ($update->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Password siswa berhasil direset.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User ID tidak ditemukan atau bukan siswa.']);
        }

    } elseif ($action === 'soft_delete') {
        // 3. Soft Delete Akun Siswa
        $target_user_id = $input['user_id'] ?? '';

        if (empty($target_user_id)) {
            echo json_encode(['status' => 'error', 'message' => 'user_id wajib diisi.']);
            exit;
        }

        $delete = $pdo->prepare("UPDATE users SET is_active = 0, deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND role = 'siswa'");
        $delete->execute([$target_user_id]);

        if ($delete->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Akun siswa berhasil dinonaktifkan.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User ID tidak ditemukan atau bukan siswa.']);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Action tidak valid. Gunakan add, reset_password, atau soft_delete.']);
    }

} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan sistem.', 'debug' => $e->getMessage()]);
}
?>