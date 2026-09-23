<?php
// scripts/backup_db.php
// Skrip ini dapat dijalankan secara manual atau dijadwalkan via Windows Task Scheduler

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // Kosong secara default di XAMPP
$dbName = 'mathventure_db';

// Lokasi folder penyimpanan backup
$backupDir = __DIR__ . '/../backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Nama file dengan format waktu (timestamp)
$date = date('Y-m-d_H-i-s');
$fileName = $backupDir . '/backup_' . $dbName . '_' . $date . '.sql';

// Path ke aplikasi mysqldump bawaan XAMPP Windows
$mysqldumpPath = 'C:\xampp\mysql\bin\mysqldump.exe';

// Susun perintah eksekusi CLI
if (empty($dbPass)) {
    $command = "\"$mysqldumpPath\" --user={$dbUser} --host={$dbHost} {$dbName} > \"{$fileName}\"";
} else {
    $command = "\"$mysqldumpPath\" --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} > \"{$fileName}\"";
}

// Jalankan perintah
exec($command, $output, $returnVar);

if ($returnVar === 0) {
    echo "✅ Backup berhasil! File disimpan di: \n" . realpath($fileName) . "\n";
} else {
    echo "❌ Backup gagal! Pastikan path mysqldump benar. Kode error: " . $returnVar . "\n";
}
?>