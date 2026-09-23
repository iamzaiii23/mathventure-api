<?php
// api/export_excel.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

session_start();

// Otorisasi khusus Peneliti
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'peneliti') {
    http_response_code(403);
    echo "Akses ditolak. Khusus Peneliti.";
    exit;
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set Header Kolom Excel
$sheet->setCellValue('A1', 'ID Sesi');
$sheet->setCellValue('B1', 'Identifier Siswa');
$sheet->setCellValue('C1', 'Nama Lengkap');
$sheet->setCellValue('D1', 'Total Skor (XP)');
$sheet->setCellValue('E1', 'Waktu Selesai');

try {
    // Tarik data gabungan dari game_sessions dan users
    $stmt = $pdo->query("
        SELECT gs.session_id, u.identifier, u.nama_lengkap, gs.total_skor, gs.waktu_selesai 
        FROM game_sessions gs 
        JOIN users u ON gs.user_id = u.id 
        WHERE gs.waktu_selesai IS NOT NULL
        ORDER BY gs.waktu_selesai DESC
    ");
    $data = $stmt->fetchAll();

    // Looping data ke baris Excel
    $row = 2;
    foreach ($data as $d) {
        $sheet->setCellValue('A' . $row, $d['session_id']);
        $sheet->setCellValue('B' . $row, $d['identifier']);
        $sheet->setCellValue('C' . $row, $d['nama_lengkap']);
        $sheet->setCellValue('D' . $row, $d['total_skor']);
        $sheet->setCellValue('E' . $row, $d['waktu_selesai']);
        $row++;
    }

    // Konfigurasi Header untuk force download .xlsx di browser
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Data_Eksperimen_Mathventure.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (\PDOException $e) {
    echo "Gagal mengekspor data: " . $e->getMessage();
}
?>