<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

// Mengambil ID transaksi dari URL
$idTransaksi = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$idTransaksi || $idTransaksi < 1) {
    header('Location: transaksi.php');
    exit;
}

try {
    // Mengambil informasi utama transaksi
    $stmt = $pdo->prepare(
        "SELECT
            t.id_transaksi,
            t.tanggal_sewa,
            t.tanggal_rencana_kembali,
            t.tanggal_kembali_aktual,
            t.status_transaksi,
            p.nama_penyewa,
            p.no_telepon,
            a.nama_admin
        FROM transaksi t
        JOIN penyewa p
            ON t.id_penyewa = p.id_penyewa
        JOIN admin a
            ON t.id_admin = a.id_admin
        WHERE t.id_transaksi = :id"
    );

    $stmt->execute([
        ':id' => $idTransaksi
    ]);

    $transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaksi) {
        header('Location: transaksi.php');
        exit;
    }

    // Mengambil daftar perlengkapan dalam transaksi
    $stmtDetail = $pdo->prepare(
        "SELECT
            dp.jumlah_disewa,
            dp.harga_sewa,
            pr.nama_perlengkapan,
            pr.jenis_perlengkapan
        FROM detail_penyewaan dp
        JOIN perlengkapan pr
            ON dp.id_perlengkapan = pr.id_perlengkapan
        WHERE dp.id_transaksi = :id"
    );

    $stmtDetail->execute([
        ':id' => $idTransaksi
    ]);

    $detailPerlengkapan = $stmtDetail->fetchAll(
        PDO::FETCH_ASSOC
    );
    // Mengambil hasil pemeriksaan kondisi setiap perlengkapan
    $stmtPemeriksaan = $pdo->prepare(
        "SELECT
            dp.id_detail,
            dp.jumlah_disewa,
            pr.nama_perlengkapan,
            pk.kondisi,
            pk.tanggal_pemeriksaan
        FROM detail_penyewaan dp
        JOIN perlengkapan pr
            ON dp.id_perlengkapan = pr.id_perlengkapan
        LEFT JOIN pemeriksaan_kondisi pk
            ON dp.id_detail = pk.id_detail
        WHERE dp.id_transaksi = :id"
    );

    $stmtPemeriksaan->execute([
        ':id' => $idTransaksi
    ]);

    $hasilPemeriksaan = $stmtPemeriksaan->fetchAll(
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Data detail transaksi gagal dimuat.');
}

// Menghitung durasi penyewaan
$tanggalMulai = new DateTimeImmutable(
    $transaksi['tanggal_sewa']
);

$tanggalKembali = new DateTimeImmutable(
    $transaksi['tanggal_rencana_kembali']
);

$durasiSewa = max(
    1,
    (int) $tanggalMulai->diff($tanggalKembali)->days
);

// Menghitung total biaya seluruh perlengkapan
$totalBiaya = 0;

foreach ($detailPerlengkapan as $detail) {
    $subtotal = (float) $detail['harga_sewa']
        * (int) $detail['jumlah_disewa']
        * $durasiSewa;

    $totalBiaya += $subtotal;
}

// Fungsi format rupiah
function formatRupiah($angka)
{
    return 'Rp ' . number_format(
        $angka,
        0,
        ',',
        '.'
    );
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Transaksi - GearTrack</title>

    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">

    <header class="header">
        <div>
            <h1>Detail Transaksi</h1>
            <p>
                Nomor Transaksi:
                #<?= (int) $transaksi['id_transaksi'] ?>
            </p>
        </div>

        <a href="transaksi.php" class="btn btn-secondary">
            Kembali
        </a>
    </header>

    <!-- Informasi transaksi -->
    <section class="card">
        <h2>Informasi Penyewaan</h2>

        <p>
            <strong>Nama Penyewa:</strong>
            <?= htmlspecialchars(
                $transaksi['nama_penyewa'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>Nomor Telepon:</strong>
            <?= htmlspecialchars(
                $transaksi['no_telepon'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>Admin:</strong>
            <?= htmlspecialchars(
                $transaksi['nama_admin'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>Tanggal Sewa:</strong>
            <?= htmlspecialchars(
                $transaksi['tanggal_sewa'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>Rencana Kembali:</strong>
            <?= htmlspecialchars(
                $transaksi['tanggal_rencana_kembali'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>Tanggal Kembali Aktual:</strong>
            <?= htmlspecialchars(
                $transaksi['tanggal_kembali_aktual'] ?? '-',
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>Status:</strong>
            <?= htmlspecialchars(
                $transaksi['status_transaksi'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>Durasi Sewa:</strong>
            <?= $durasiSewa ?> hari
        </p>
    </section>

    <!-- Daftar perlengkapan -->
    <section class="card">
        <h2>Daftar Perlengkapan</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Perlengkapan</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Harga per Hari</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($detailPerlengkapan)): ?>

                        <tr>
                            <td colspan="6">
                                Belum ada detail perlengkapan.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($detailPerlengkapan as $index => $detail): ?>

                            <?php
                            $subtotal = (float) $detail['harga_sewa']
                                * (int) $detail['jumlah_disewa']
                                * $durasiSewa;
                            ?>

                            <tr>
                                <td><?= $index + 1 ?></td>

                                <td>
                                    <?= htmlspecialchars(
                                        $detail['nama_perlengkapan'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $detail['jenis_perlengkapan'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= (int) $detail['jumlah_disewa'] ?>
                                </td>

                                <td>
                                    <?= formatRupiah(
                                        $detail['harga_sewa']
                                    ) ?>
                                </td>

                                <td>
                                    <?= formatRupiah($subtotal) ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="5" style="text-align: right;">
                            Total Biaya
                        </th>

                        <th>
                            <?= formatRupiah($totalBiaya) ?>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>

    <!-- Hasil pemeriksaan kondisi -->
    <section class="card">
        <h2>Hasil Pemeriksaan Kondisi</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Perlengkapan</th>
                        <th>Jumlah</th>
                        <th>Kondisi</th>
                        <th>Tanggal Pemeriksaan</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($hasilPemeriksaan)): ?>

                        <tr>
                            <td colspan="5">
                                Belum ada detail perlengkapan untuk diperiksa.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($hasilPemeriksaan as $index => $hasil): ?>

                            <tr>
                                <td><?= $index + 1 ?></td>

                                <td>
                                    <?= htmlspecialchars(
                                        $hasil['nama_perlengkapan'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= (int) $hasil['jumlah_disewa'] ?>
                                </td>

                                <td>
                                    <?php if ($hasil['kondisi'] === null): ?>

                                        Belum diperiksa

                                    <?php else: ?>

                                        <?= htmlspecialchars(
                                            $hasil['kondisi'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $hasil['tanggal_pemeriksaan'] ?? '-',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

</body>
</html>