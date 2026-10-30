
<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: penyewa.php');
    exit;
}

$id = filter_input(
    INPUT_POST,
    'id_penyewa',
    FILTER_VALIDATE_INT
);

if ($id === false || $id === null || $id < 1) {
    header('Location: penyewa.php?status=id_tidak_valid');
    exit;
}

try {
    // Periksa apakah penyewa sudah memiliki transaksi
    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM transaksi
         WHERE id_penyewa = :id"
    );

    $stmt->execute([':id' => $id]);
    $jumlahTransaksi = (int) $stmt->fetchColumn();

    if ($jumlahTransaksi > 0) {
        header('Location: penyewa.php?status=penyewa_memiliki_transaksi');
        exit;
    }

    // Hapus hanya jika penyewa belum memiliki transaksi
    $stmt = $pdo->prepare(
        "DELETE FROM penyewa
         WHERE id_penyewa = :id"
    );

    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() > 0) {
        header('Location: penyewa.php?status=hapus_berhasil');
    } else {
        header('Location: penyewa.php?status=data_tidak_ditemukan');
    }

    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());
    header('Location: penyewa.php?status=hapus_gagal');
    exit;
}
