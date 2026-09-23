session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'peneliti') {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Anda bukan Peneliti.']);
    exit;
}