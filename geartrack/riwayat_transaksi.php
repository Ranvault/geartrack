<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

$sql = $keyword = trim($_GET['keyword'] ?? '');
$status = $_GET['status'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';

$sql = "
    SELECT
        t.id_transaksi,
        p.nama_penyewa,
        a.nama_admin,
        t.tanggal_sewa,
        t.tanggal_rencana_kembali,
        t.tanggal_kembali_aktual,
        t.status_transaksi,
        COALESCE(SUM(dp.jumlah_disewa), 0) AS total_barang
    FROM transaksi t
    JOIN penyewa p
        ON t.id_penyewa = p.id_penyewa
    JOIN admin a
        ON t.id_admin = a.id_admin
    LEFT JOIN detail_penyewaan dp
        ON t.id_transaksi = dp.id_transaksi
    WHERE 1 = 1
";

$params = [];

if ($keyword !== '') {
    $sql .= " AND p.nama_penyewa LIKE :keyword";
    $params[':keyword'] = '%' . $keyword . '%';
}

if (in_array($status, ['Disewa', 'Dikembalikan'], true)) {
    $sql .= " AND t.status_transaksi = :status";
    $params[':status'] = $status;
}

if ($tanggal !== '') {
    $tanggalValid = DateTime::createFromFormat('!Y-m-d', $tanggal);

    if ($tanggalValid && $tanggalValid->format('Y-m-d') === $tanggal) {
        $sql .= " AND t.tanggal_sewa = :tanggal";
        $params[':tanggal'] = $tanggal;
    }
}

$sql .= "
    GROUP BY
        t.id_transaksi,
        p.nama_penyewa,
        a.nama_admin,
        t.tanggal_sewa,
        t.tanggal_rencana_kembali,
        t.tanggal_kembali_aktual,
        t.status_transaksi
    ORDER BY t.id_transaksi DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Transaksi - GearTrack</title>

    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">

    <header class="header">
        <div>
            <h1>Riwayat Transaksi</h1>
            <p>Daftar seluruh transaksi penyewaan perlengkapan.</p>
        </div>

        <a href="transaksi.php" class="btn btn-secondary">
            Kembali
        </a>
    </header>

    <section class="card">
        <form method="GET" action="riwayat_transaksi.php">
    <div class="form-group">
        <label for="keyword">Nama Penyewa</label>
        <input
            type="text"
            id="keyword"
            name="keyword"
            placeholder="Cari nama penyewa..."
            value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
        >
    </div>

    <div class="form-group">
        <label for="status">Status Transaksi</label>
        <select id="status" name="status">
            <option value="">Semua Status</option>
            <option value="Disewa" <?= $status === 'Disewa' ? 'selected' : '' ?>>
                Disewa
            </option>
            <option value="Dikembalikan" <?= $status === 'Dikembalikan' ? 'selected' : '' ?>>
                Dikembalikan
            </option>
        </select>
    </div>

    <div class="form-group">
        <label for="tanggal">Tanggal Sewa</label>
        <input
            type="date"
            id="tanggal"
            name="tanggal"
            value="<?= htmlspecialchars($tanggal, ENT_QUOTES, 'UTF-8') ?>"
        >
    </div>

    <button type="submit" class="btn">Cari</button>
    <a href="riwayat_transaksi.php" class="btn btn-secondary">Reset</a>
</form>

<hr>
        <?php if (empty($transaksi)): ?>

            <p>Belum ada transaksi penyewaan.</p>

        <?php else: ?>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Nama Penyewa</th>
                            <th>Admin</th>
                            <th>Tanggal Sewa</th>
                            <th>Rencana Kembali</th>
                            <th>Pengembalian Aktual</th>
                            <th>Total Barang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($transaksi as $row): ?>
                            <tr>
                                <td>
                                    <?= (int) $row['id_transaksi'] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['nama_penyewa'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['nama_admin'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['tanggal_sewa'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['tanggal_rencana_kembali'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['tanggal_kembali_aktual'] ?? '-',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= (int) $row['total_barang'] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['status_transaksi'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>
                                <td>
                                    <a
                                        href="detail_riwayat.php?id=<?= (int) $row['id_transaksi'] ?>"
                                        class="btn btn-secondary"
                                    > Detail
                                    </a>
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