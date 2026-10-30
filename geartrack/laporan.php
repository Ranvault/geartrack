<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

// Ambil filter tanggal dari URL
$tanggalMulai = $_GET['tanggal_mulai'] ?? '';
$tanggalAkhir = $_GET['tanggal_akhir'] ?? '';

// Validasi format tanggal
function tanggalValid(?string $tanggal): bool
{
    if ($tanggal === '') {
        return true;
    }

    $hasil = DateTime::createFromFormat('!Y-m-d', $tanggal);

    return $hasil !== false
        && $hasil->format('Y-m-d') === $tanggal;
}

if (!tanggalValid($tanggalMulai) || !tanggalValid($tanggalAkhir)) {
    exit('Format tanggal tidak valid.');
}

// Validasi rentang tanggal
if (
    $tanggalMulai !== ''
    && $tanggalAkhir !== ''
    && $tanggalMulai > $tanggalAkhir
) {
    exit('Tanggal mulai tidak boleh melebihi tanggal akhir.');
}

// Siapkan kondisi filter tanggal
$where = [];
$params = [];

if ($tanggalMulai !== '') {
    $where[] = 't.tanggal_sewa >= :tanggal_mulai';
    $params[':tanggal_mulai'] = $tanggalMulai;
}

if ($tanggalAkhir !== '') {
    $where[] = 't.tanggal_sewa <= :tanggal_akhir';
    $params[':tanggal_akhir'] = $tanggalAkhir;
}

$kondisiTanggal = $where
    ? ' WHERE ' . implode(' AND ', $where)
    : '';


// 1. Ringkasan transaksi
$sqlRingkasan = "
    SELECT
        COUNT(*) AS total_transaksi,
        COALESCE(SUM(
            CASE
                WHEN t.status_transaksi = 'Disewa'
                THEN 1 ELSE 0
            END
        ), 0) AS transaksi_berlangsung,
        COALESCE(SUM(
            CASE
                WHEN t.status_transaksi = 'Dikembalikan'
                THEN 1 ELSE 0
            END
        ), 0) AS transaksi_selesai
    FROM transaksi t
    $kondisiTanggal
";

$stmt = $pdo->prepare($sqlRingkasan);
$stmt->execute($params);
$ringkasan = $stmt->fetch(PDO::FETCH_ASSOC);


// 2. Ringkasan jumlah unit dan nilai penyewaan
$sqlBarang = "
    SELECT
        COALESCE(SUM(dp.jumlah_disewa), 0) AS total_unit,
        COALESCE(
            SUM(dp.jumlah_disewa * dp.harga_sewa),
            0
        ) AS total_nilai
    FROM transaksi t
    JOIN detail_penyewaan dp
        ON t.id_transaksi = dp.id_transaksi
    $kondisiTanggal
";

$stmt = $pdo->prepare($sqlBarang);
$stmt->execute($params);
$barang = $stmt->fetch(PDO::FETCH_ASSOC);


// 3. Rekap transaksi berdasarkan status
$sqlStatus = "
    SELECT
        t.status_transaksi,
        COUNT(DISTINCT t.id_transaksi) AS jumlah_transaksi,
        COALESCE(SUM(dp.jumlah_disewa), 0) AS total_unit,
        COALESCE(
            SUM(dp.jumlah_disewa * dp.harga_sewa),
            0
        ) AS total_nilai
    FROM transaksi t
    LEFT JOIN detail_penyewaan dp
        ON t.id_transaksi = dp.id_transaksi
    $kondisiTanggal
    GROUP BY t.status_transaksi
    ORDER BY t.status_transaksi ASC
";

$stmt = $pdo->prepare($sqlStatus);
$stmt->execute($params);
$rekapStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Laporan Penyewaan - GearTrack</title>

    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">

    <header class="header">
        <div>
            <h1>Laporan Penyewaan</h1>
            <p>Ringkasan aktivitas penyewaan perlengkapan GearTrack.</p>
        </div>

        <a href="index.php" class="btn btn-secondary">
            Kembali ke Dashboard
        </a>
    </header>

    <!-- Filter periode laporan -->
    <section class="card">
        <h2>Filter Periode Laporan</h2>

        <form method="GET" action="laporan.php">

            <div class="form-group">
                <label for="tanggal_mulai">Tanggal Mulai</label>

                <input
                    type="date"
                    id="tanggal_mulai"
                    name="tanggal_mulai"
                    value="<?= htmlspecialchars(
                        $tanggalMulai,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >
            </div>

            <div class="form-group">
                <label for="tanggal_akhir">Tanggal Akhir</label>

                <input
                    type="date"
                    id="tanggal_akhir"
                    name="tanggal_akhir"
                    value="<?= htmlspecialchars(
                        $tanggalAkhir,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >
            </div>

            <button type="submit" class="btn">
                Tampilkan Laporan
            </button>

            <a href="laporan.php" class="btn btn-secondary">
                Reset
            </a>

        </form>
    </section>

    <!-- Ringkasan transaksi -->
    <section class="card">
        <h2>Ringkasan Transaksi</h2>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Total Transaksi</th>
                        <th>Sedang Disewa</th>
                        <th>Sudah Dikembalikan</th>
                        <th>Total Unit Disewa</th>
                        <th>Total Nilai Penyewaan</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>
                            <?= (int) $ringkasan['total_transaksi'] ?>
                        </td>

                        <td>
                            <?= (int) $ringkasan['transaksi_berlangsung'] ?>
                        </td>

                        <td>
                            <?= (int) $ringkasan['transaksi_selesai'] ?>
                        </td>

                        <td>
                            <?= (int) $barang['total_unit'] ?>
                        </td>

                        <td>
                            Rp <?= number_format(
                                (float) $barang['total_nilai'],
                                0,
                                ',',
                                '.'
                            ) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Rekap berdasarkan status -->
    <section class="card">
        <h2>Rekap Berdasarkan Status</h2>

        <?php if (empty($rekapStatus)): ?>

            <p>Tidak ada transaksi pada periode yang dipilih.</p>

        <?php else: ?>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Jumlah Transaksi</th>
                            <th>Total Unit</th>
                            <th>Total Nilai Penyewaan</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($rekapStatus as $row): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars(
                                        $row['status_transaksi'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= (int) $row['jumlah_transaksi'] ?>
                                </td>

                                <td>
                                    <?= (int) $row['total_unit'] ?>
                                </td>

                                <td>
                                    Rp <?= number_format(
                                        (float) $row['total_nilai'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </section>

</div>

</body>
</html>