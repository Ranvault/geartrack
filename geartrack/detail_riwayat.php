<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

$idTransaksi = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$idTransaksi || $idTransaksi < 1) {
    header('Location: riwayat_transaksi.php');
    exit;
}

// Ambil informasi transaksi
$stmt = $pdo->prepare("
    SELECT
        t.id_transaksi,
        p.nama_penyewa,
        p.no_telepon,
        a.nama_admin,
        t.tanggal_sewa,
        t.tanggal_rencana_kembali,
        t.tanggal_kembali_aktual,
        t.status_transaksi
    FROM transaksi t
    JOIN penyewa p
        ON t.id_penyewa = p.id_penyewa
    JOIN admin a
        ON t.id_admin = a.id_admin
    WHERE t.id_transaksi = :id
");

$stmt->execute([':id' => $idTransaksi]);
$transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaksi) {
    exit('Transaksi tidak ditemukan.');
}

// Ambil daftar barang dan hasil pemeriksaan kondisi
$stmt = $pdo->prepare("
    SELECT
        dp.id_detail,
        pl.nama_perlengkapan,
        dp.jumlah_disewa,
        dp.harga_sewa,
        pk.kondisi,
        pk.tanggal_pemeriksaan
    FROM detail_penyewaan dp
    JOIN perlengkapan pl
        ON dp.id_perlengkapan = pl.id_perlengkapan
    LEFT JOIN pemeriksaan_kondisi pk
        ON dp.id_detail = pk.id_detail
    WHERE dp.id_transaksi = :id
    ORDER BY dp.id_detail ASC
");

$stmt->execute([':id' => $idTransaksi]);
$barang = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalBiaya = 0;

foreach ($barang as $item) {
    $totalBiaya +=
        (int) $item['jumlah_disewa'] *
        (float) $item['harga_sewa'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Riwayat Transaksi - GearTrack</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">

    <header class="header">
        <div>
            <h1>Detail Riwayat Transaksi</h1>
            <p>Transaksi #<?= (int) $transaksi['id_transaksi'] ?></p>
        </div>

        <a href="riwayat_transaksi.php" class="btn btn-secondary">
            Kembali ke Riwayat
        </a>
    </header>

    <section class="card">
        <h2>Informasi Transaksi</h2>

        <p><strong>Nama penyewa:</strong>
            <?= htmlspecialchars($transaksi['nama_penyewa'], ENT_QUOTES, 'UTF-8') ?>
        </p>

        <p><strong>No. telepon:</strong>
            <?= htmlspecialchars($transaksi['no_telepon'], ENT_QUOTES, 'UTF-8') ?>
        </p>

        <p><strong>Admin:</strong>
            <?= htmlspecialchars($transaksi['nama_admin'], ENT_QUOTES, 'UTF-8') ?>
        </p>

        <p><strong>Tanggal sewa:</strong>
            <?= htmlspecialchars($transaksi['tanggal_sewa'], ENT_QUOTES, 'UTF-8') ?>
        </p>

        <p><strong>Rencana kembali:</strong>
            <?= htmlspecialchars($transaksi['tanggal_rencana_kembali'], ENT_QUOTES, 'UTF-8') ?>
        </p>

        <p><strong>Pengembalian aktual:</strong>
            <?= htmlspecialchars($transaksi['tanggal_kembali_aktual'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
        </p>

        <p><strong>Status:</strong>
            <?= htmlspecialchars($transaksi['status_transaksi'], ENT_QUOTES, 'UTF-8') ?>
        </p>
    </section>

    <section class="card">
        <h2>Daftar Perlengkapan</h2>

        <?php if (empty($barang)): ?>
            <p>Tidak ada detail perlengkapan untuk transaksi ini.</p>
        <?php else: ?>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Perlengkapan</th>
                            <th>Jumlah</th>
                            <th>Harga Sewa per Barang</th>
                            <th>Subtotal</th>
                            <th>Kondisi</th>
                            <th>Tanggal Pemeriksaan</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($barang as $item): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($item['nama_perlengkapan'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td><?= (int) $item['jumlah_disewa'] ?></td>

                                <td>
                                    Rp <?= number_format((float) $item['harga_sewa'], 0, ',', '.') ?>
                                </td>

                                <td>
                                    Rp <?= number_format(
                                        (int) $item['jumlah_disewa'] * (float) $item['harga_sewa'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($item['kondisi'] ?? 'Belum diperiksa', ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($item['tanggal_pemeriksaan'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <h3>
                Total Biaya Sewa:
                Rp <?= number_format($totalBiaya, 0, ',', '.') ?>
            </h3>

            <p>
                <small>
                    Total di atas merupakan jumlah harga sewa yang tersimpan
                    dikalikan jumlah barang. Perhitungan ini belum memasukkan
                    durasi sewa atau biaya tambahan.
                </small>
            </p>

        <?php endif; ?>
    </section>

</div>

</body>
</html>