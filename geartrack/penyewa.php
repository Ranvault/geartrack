<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/bahasa.php';

// Mengambil data penyewa
try {
    $sql = "SELECT id_penyewa, nama_penyewa, no_telepon
            FROM penyewa
            ORDER BY id_penyewa DESC";

    $stmt = $pdo->query($sql);
    $dataPenyewa = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());

    exit(
        $_SESSION['bahasa'] === 'en'
            ? 'Failed to load renter data.'
            : 'Data penyewa gagal dimuat.'
    );
}

// Notifikasi
$status = $_GET['status'] ?? '';

$pesan = [
    'hapus_berhasil' => 'penyewa_berhasil_dihapus',
    'penyewa_memiliki_transaksi' => 'penyewa_memiliki_transaksi',
    'id_tidak_valid' => 'id_tidak_valid',
    'data_tidak_ditemukan' => 'data_tidak_ditemukan',
    'hapus_gagal' => 'penyewa_gagal_dihapus'
];

$jenisPesan = in_array(
    $status,
    [
        'penyewa_memiliki_transaksi',
        'id_tidak_valid',
        'data_tidak_ditemukan',
        'hapus_gagal'
    ],
    true
) ? 'error' : 'success';
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['bahasa'] ?? 'id', ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars(t('judul_penyewa'), ENT_QUOTES, 'UTF-8') ?>
        - GearTrack
    </title>

    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<div class="container">

    <!-- Header -->
    <header class="header">

        <div>
            <h1>
                <?= htmlspecialchars(t('judul_penyewa'), ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <p>
                <?= htmlspecialchars(t('deskripsi_penyewa'), ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>

        <a href="index.php" class="btn btn-secondary">
            <?= htmlspecialchars(t('kembali_dashboard'), ENT_QUOTES, 'UTF-8') ?>
        </a>

    </header>

    <!-- Data Penyewa -->
    <section class="card">

        <!-- Notifikasi -->
        <?php if (isset($pesan[$status])): ?>

            <div class="alert <?= $jenisPesan === 'error' ? 'alert-error' : '' ?>">

                <?= htmlspecialchars(
                    t($pesan[$status]),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>

        <h2>
            <?= htmlspecialchars(t('daftar_penyewa'), ENT_QUOTES, 'UTF-8') ?>
        </h2>

        <p>
            <a href="tambah_penyewa.php" class="btn">
                + <?= htmlspecialchars(t('tambah_penyewa'), ENT_QUOTES, 'UTF-8') ?>
            </a>
        </p>

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th><?= htmlspecialchars(t('no'), ENT_QUOTES, 'UTF-8') ?></th>

                        <th><?= htmlspecialchars(t('nama_penyewa'), ENT_QUOTES, 'UTF-8') ?></th>

                        <th><?= htmlspecialchars(t('no_telepon'), ENT_QUOTES, 'UTF-8') ?></th>

                        <th><?= htmlspecialchars(t('aksi'), ENT_QUOTES, 'UTF-8') ?></th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($dataPenyewa)): ?>

                        <tr>
                            <td colspan="4">
                                <?= htmlspecialchars(t('belum_ada_data_penyewa'), ENT_QUOTES, 'UTF-8') ?>
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($dataPenyewa as $nomor => $penyewa): ?>

                            <tr>

                                <td>
                                    <?= $nomor + 1 ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $penyewa['nama_penyewa'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $penyewa['no_telepon'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>

                                    <div class="actions">

                                        <!-- Edit -->
                                        <a
                                            href="edit_penyewa.php?id=<?= (int) $penyewa['id_penyewa'] ?>"
                                            class="btn"
                                        >
                                            <?= htmlspecialchars(t('edit'), ENT_QUOTES, 'UTF-8') ?>
                                        </a>

                                        <!-- Hapus -->
                                        <form
                                            method="POST"
                                            action="hapus_penyewa.php"
                                            onsubmit="return confirm('<?= htmlspecialchars(t('konfirmasi_hapus_penyewa'), ENT_QUOTES, 'UTF-8') ?>');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id_penyewa"
                                                value="<?= (int) $penyewa['id_penyewa'] ?>"
                                            >

                                            <button type="submit" class="btn btn-danger">
                                                <?= htmlspecialchars(t('hapus'), ENT_QUOTES, 'UTF-8') ?>
                                            </button>

                                        </form>

                                    </div>

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