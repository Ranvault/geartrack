<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

// Pastikan ID transaksi valid
$idTransaksi = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$idTransaksi || $idTransaksi < 1) {
    header('Location: pengembalian.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Ambil transaksi dan kunci baris selama proses berlangsung
        $stmt = $pdo->prepare(
            "SELECT id_transaksi, status_transaksi
             FROM transaksi
             WHERE id_transaksi = :id
             FOR UPDATE"
        );

        $stmt->execute([
            ':id' => $idTransaksi
        ]);

        $transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$transaksi) {
            throw new RuntimeException(
                'Transaksi tidak ditemukan.'
            );
        }

        if ($transaksi['status_transaksi'] !== 'Disewa') {
            throw new RuntimeException(
                'Transaksi ini sudah tidak berstatus Disewa.'
            );
        }

        // Catat tanggal kembali aktual dan ubah status
        $stmt = $pdo->prepare(
            "UPDATE transaksi
             SET tanggal_kembali_aktual = :tanggal,
                 status_transaksi = 'Dikembalikan'
             WHERE id_transaksi = :id"
        );

        $stmt->execute([
            ':tanggal' => date('Y-m-d'),
            ':id' => $idTransaksi
        ]);

        $pdo->commit();

        header(
            'Location: pemeriksaan_kondisi.php?id=' . $idTransaksi
        );
        exit;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if ($e instanceof PDOException) {
            error_log($e->getMessage());
            $error = 'Pengembalian gagal diproses karena kesalahan database.';
        } else {
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pengembalian - GearTrack</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">
    <section class="card">
        <h1>Konfirmasi Pengembalian</h1>

        <p>
            Apakah kamu yakin ingin mencatat transaksi
            #<?= (int) $idTransaksi ?> sebagai sudah dikembalikan?
        </p>

        <p>
            Tanggal kembali aktual akan diisi menggunakan tanggal hari ini.
        </p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>
        <?php endif; ?>

        <form method="POST"
              action="proses_pengembalian.php?id=<?= (int) $idTransaksi ?>">

            <button type="submit" class="btn">
                Ya, Barang Sudah Dikembalikan
            </button>

            <a href="pengembalian.php" class="btn btn-secondary">
                Batal
            </a>
        </form>
    </section>
</div>

</body>
</html>