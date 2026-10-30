
<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

try {
    $sql = "SELECT
                t.id_transaksi,
                p.nama_penyewa,
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
            ORDER BY t.id_transaksi DESC";

    $stmt = $pdo->query($sql);
    $dataTransaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Data transaksi gagal dimuat.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Transaksi - GearTrack</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">

    <header class="header">
        <div>
            <h1>Data Transaksi</h1>
            <p>Kelola penyewaan perlengkapan outdoor.</p>
        </div>

        <a href="index.php" class="btn btn-secondary">
            Kembali ke Dashboard
        </a>
    </header>

    <section class="card">
        <h2>Daftar Transaksi Penyewaan</h2>
        <a href="tambah_transaksi.php" class="btn">+ Buat Transaksi</a>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Penyewa</th>
                        <th>Admin</th>
                        <th>Tanggal Sewa</th>
                        <th>Rencana Kembali</th>
                        <th>Kembali Aktual</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($dataTransaksi)): ?>
                        <tr>
                            <td colspan="7">
                                Belum ada transaksi penyewaan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dataTransaksi as $transaksi): ?>
                            <tr>
                                <td>
                                    <?= (int) $transaksi['id_transaksi'] ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars(
                                        $transaksi['nama_penyewa'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars(
                                        $transaksi['nama_admin'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($transaksi['tanggal_sewa'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($transaksi['tanggal_rencana_kembali'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td>
                                    <?= $transaksi['tanggal_kembali_aktual'] !== null
                                        ? htmlspecialchars($transaksi['tanggal_kembali_aktual'], ENT_QUOTES, 'UTF-8')
                                        : '-' ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars(
                                        $transaksi['status_transaksi'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>
                                <td>
                                    <a
                                        href="detail_transaksi.php?id=<?= (int) $transaksi['id_transaksi'] ?>"
                                        class="btn"
                                    >Detail
                                    </a>
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
