<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

$idTransaksi = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$idTransaksi || $idTransaksi < 1) {
    header('Location: pengembalian.php');
    exit;
}

// Ambil data transaksi yang sudah dikembalikan
$stmt = $pdo->prepare(
    "SELECT
        t.id_transaksi,
        t.tanggal_kembali_aktual,
        t.status_transaksi,
        p.nama_penyewa
     FROM transaksi t
     JOIN penyewa p
        ON t.id_penyewa = p.id_penyewa
     WHERE t.id_transaksi = :id
       AND t.status_transaksi = 'Dikembalikan'"
);

$stmt->execute([
    ':id' => $idTransaksi
]);

$transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaksi) {
    exit('Transaksi tidak ditemukan atau belum dikembalikan.');
}

// Ambil daftar barang dalam transaksi
$stmt = $pdo->prepare(
    "SELECT
        dp.id_detail,
        dp.jumlah_disewa,
        p.id_perlengkapan,
        p.nama_perlengkapan
     FROM detail_penyewaan dp
     JOIN perlengkapan p
        ON dp.id_perlengkapan = p.id_perlengkapan
     WHERE dp.id_transaksi = :id
     ORDER BY p.nama_perlengkapan ASC"
);

$stmt->execute([
    ':id' => $idTransaksi
]);

$dataBarang = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pemeriksaan Kondisi Barang - GearTrack</title>

    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">

    <header class="header">
        <div>
            <h1>Pemeriksaan Kondisi Barang</h1>
            <p>
                Transaksi #<?= (int) $transaksi['id_transaksi'] ?>
            </p>
        </div>

        <a href="transaksi.php" class="btn btn-secondary">
            Kembali
        </a>
    </header>

    <section class="card">
        <h2>Informasi Transaksi</h2>

        <p>
            <strong>Nama penyewa:</strong>
            <?= htmlspecialchars(
                $transaksi['nama_penyewa'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>Tanggal pengembalian:</strong>
            <?= htmlspecialchars(
                $transaksi['tanggal_kembali_aktual'] ?? '-',
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>
        <form method="POST" action="simpan_pemeriksaan.php">
        <input
        type="hidden"
        name="id_transaksi"
        value="<?= (int) $idTransaksi ?>">

        <h2>Daftar Perlengkapan</h2>

        <?php if (empty($dataBarang)): ?>
            <p>Tidak ada perlengkapan dalam transaksi ini.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Perlengkapan</th>
                            <th>Jumlah Dikembalikan</th>
                            <th>Kondisi Barang</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataBarang as $barang): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars(
                                        $barang['nama_perlengkapan'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= (int) $barang['jumlah_disewa'] ?>
                                </td>

                                <td>
                                    <select
                                        name="kondisi[<?= (int) $barang['id_detail'] ?>]"
                                        required
                                    >
                                        <option value="">-- Pilih Kondisi --</option>
                                        <option value="Layak">Layak</option>
                                        <option value="Perlu Perawatan">Perlu Perawatan</option>
                                        <option value="Tidak Layak">Tidak Layak</option>
                                    </select>
                                </td>

                                <td>
                                    Pilih kondisi barang
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
                <button type="submit" class="btn">
            Simpan Hasil Pemeriksaan
        </button>
    </form>
    </section>

</div>

</body>
</html>