<?php
// api/logout.php
session_start();

// Hapus semua variabel sesi
$_SESSION = [];

// Hancurkan sesi sepenuhnya
session_destroy();

// Hapus cookie sesi jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'Logout berhasil. Sesi telah dihancurkan.'
]);
?>