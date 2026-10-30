<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Proses pergantian bahasa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $bahasa = $_POST['bahasa'] ?? 'id';

    // Hanya menerima bahasa Indonesia atau Inggris
    if (in_array($bahasa, ['id', 'en'], true)) {
        $_SESSION['bahasa'] = $bahasa;
    }
}

// Halaman tujuan setelah pergantian bahasa
$kembali = $_POST['kembali'] ?? 'index.php';

// Validasi tujuan agar hanya menuju halaman lokal
if (
    !is_string($kembali) ||
    $kembali === '' ||
    str_contains($kembali, '://') ||
    str_starts_with($kembali, '//') ||
    str_contains($kembali, "\r") ||
    str_contains($kembali, "\n") ||
    str_contains($kembali, '\\') ||
    str_starts_with($kembali, '/')
) {
    $kembali = 'index.php';
}

header('Location: ' . $kembali);
exit;
?>