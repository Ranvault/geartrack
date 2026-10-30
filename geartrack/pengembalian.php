 <?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

$stmt = $pdo->query(
    "SELECT
        t.id_transaksi,
        p.nama_penyewa,
        t.tanggal_sewa,
        t.tanggal_rencana_kembali,
        t.status_transaksi
     FROM transaksi t
     JOIN penyewa p ON t.id_penyewa = p.id_penyewa
     WHERE t.status_transaksi = 'Disewa'
     ORDER BY t.tanggal_rencana_kembali ASC"
);

$dataTransaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian Barang - GearTrack</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">
    <header class="header">
        <div>
            <h1>Pengembalian Barang</h1>
            <p>Daftar transaksi yang belum dikembalikan.</p>
        </div>

        <a href="transaksi.php" class="btn btn-secondary">
            Kembali
        </a>
    </header>

    <section class="card">
        <?php if (empty($dataTransaksi)): ?>
            <p>Tidak ada transaksi yang menunggu pengembalian.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Nama Penyewa</th>
                            <th>Tanggal Sewa</th>
                            <th>Rencana Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
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
                                        $transaksi['tanggal_sewa'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars(
                                        $transaksi['tanggal_rencana_kembali'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
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
                                        href="proses_pengembalian.php?id=<?= (int) $transaksi['id_transaksi'] ?>"
                                        class="btn"
                                    >
                                        Kembalikan
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