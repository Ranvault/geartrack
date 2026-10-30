<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/bahasa.php';

// Mengambil data perlengkapan
try {
    $stmt = $pdo->query(
        "SELECT
            id_perlengkapan,
            nama_perlengkapan,
            jenis_perlengkapan,
            stok_total,
            stok_perawatan,
            stok_tidak_layak,
            harga_perhari
        FROM perlengkapan
    ");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Menghitung jumlah barang yang sedang disewa
    $stmtDisewa = $pdo->query(
        "SELECT
            dp.id_perlengkapan,
            SUM(dp.jumlah_disewa) AS jumlah_disewa
        FROM detail_penyewaan dp
        JOIN transaksi t
            ON dp.id_transaksi = t.id_transaksi
        WHERE t.status_transaksi = 'Disewa'
        GROUP BY dp.id_perlengkapan
    ");

    $dataDisewa = $stmtDisewa->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Data perlengkapan gagal dimuat.');
}

// Notifikasi CRUD
$status = $_GET['status'] ?? '';

$pesan = [
    'berhasil' => 'data_berhasil_ditambahkan',
    'edit_berhasil' => 'data_berhasil_diperbarui',
    'hapus_berhasil' => 'data_berhasil_dihapus',
    'data_tidak_ditemukan' => 'data_tidak_ditemukan',
    'id_tidak_valid' => 'id_tidak_valid',
    'hapus_gagal' => 'data_gagal_dihapus'
];

$jenisPesan = in_array(
    $status,
    ['data_tidak_ditemukan', 'id_tidak_valid', 'hapus_gagal'],
    true
) ? 'error' : 'success';

// Mengambil bahasa aktif
$bahasaAktif = $_SESSION['bahasa'] ?? 'id';
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($bahasaAktif, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars(t('judul_aplikasi'), ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars(t('dashboard'), ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<div class="container">

    <!-- Header -->
    <header class="header">

        <div>
            <h1><?= htmlspecialchars(t('judul_aplikasi'), ENT_QUOTES, 'UTF-8') ?></h1>

            <p>
                <?= htmlspecialchars(t('deskripsi_aplikasi'), ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>

        <div>

            <!-- Pilihan Bahasa -->
            <form
                action="ubah_bahasa.php"
                method="POST"
                style="margin-bottom: 10px;"
            >
                <label for="bahasa">
                    <?= htmlspecialchars(t('bahasa'), ENT_QUOTES, 'UTF-8') ?>:
                </label>

                <select
                    name="bahasa"
                    id="bahasa"
                    onchange="this.form.submit()"
                >
                    <option
                        value="id"
                        <?= $bahasaAktif === 'id' ? 'selected' : '' ?>
                    >
                        Indonesia
                    </option>

                    <option
                        value="en"
                        <?= $bahasaAktif === 'en' ? 'selected' : '' ?>
                    >
                        English
                    </option>
                </select>

                <input
                    type="hidden"
                    name="kembali"
                    value="index.php"
                >
            </form>

            <!-- Informasi Admin -->
            <p>
                <?= htmlspecialchars(t('selamat_datang'), ENT_QUOTES, 'UTF-8') ?>,
                <strong>
                    <?= htmlspecialchars(
                        $_SESSION['nama_admin'] ?? 'Admin',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </strong>
            </p>

            <br>

            <a
                class="btn btn-secondary"
                href="logout.php"
                onclick="return confirm('<?= htmlspecialchars(t('konfirmasi_logout'), ENT_QUOTES, 'UTF-8') ?>')"
            >
                <?= htmlspecialchars(t('keluar'), ENT_QUOTES, 'UTF-8') ?>
            </a>

        </div>

    </header>

    <!-- Menu Utama -->
    <section class="card">

        <h2><?= htmlspecialchars(t('dashboard'), ENT_QUOTES, 'UTF-8') ?></h2>

        <p>
            <?= htmlspecialchars(t('deskripsi_perlengkapan'), ENT_QUOTES, 'UTF-8') ?>
        </p>

        <div class="actions">

            <a class="btn" href="penyewa.php">
                <?= htmlspecialchars(t('penyewa'), ENT_QUOTES, 'UTF-8') ?>
            </a>

            <a class="btn" href="tambah_perlengkapan.php">
                + <?= htmlspecialchars(t('tambah_perlengkapan'), ENT_QUOTES, 'UTF-8') ?>
            </a>

            <a class="btn" href="transaksi.php">
                <?= htmlspecialchars(t('transaksi'), ENT_QUOTES, 'UTF-8') ?>
            </a>

            <a class="btn" href="pengembalian.php">
                <?= htmlspecialchars(t('pengembalian'), ENT_QUOTES, 'UTF-8') ?>
            </a>

        </div>

    </section>

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

    <!-- Tabel Perlengkapan -->
    <section class="card">

        <h2>
            <?= htmlspecialchars(t('judul_perlengkapan'), ENT_QUOTES, 'UTF-8') ?>
        </h2>

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th><?= htmlspecialchars(t('no'), ENT_QUOTES, 'UTF-8') ?></th>

                        <th><?= htmlspecialchars(t('nama_perlengkapan'), ENT_QUOTES, 'UTF-8') ?></th>

                        <th><?= htmlspecialchars(t('jenis'), ENT_QUOTES, 'UTF-8') ?></th>

                        <th><?= htmlspecialchars(t('stok_total'), ENT_QUOTES, 'UTF-8') ?></th>

                        <th><?= htmlspecialchars(t('stok_tersedia'), ENT_QUOTES, 'UTF-8') ?></th>

                        <th><?= htmlspecialchars(t('status_stok'), ENT_QUOTES, 'UTF-8') ?></th>

                        <th><?= htmlspecialchars(t('harga_perhari'), ENT_QUOTES, 'UTF-8') ?></th>

                        <th><?= htmlspecialchars(t('aksi'), ENT_QUOTES, 'UTF-8') ?></th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($data)): ?>

                        <tr>
                            <td colspan="8">
                                <?= htmlspecialchars(t('belum_ada_data'), ENT_QUOTES, 'UTF-8') ?>
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($data as $index => $baris): ?>

                            <?php
                                $stokDisewa = (int) (
                                    $dataDisewa[$baris['id_perlengkapan']] ?? 0
                                );

                                $stokTersedia = (int) $baris['stok_total']
                                    - (int) $baris['stok_perawatan']
                                    - (int) $baris['stok_tidak_layak']
                                    - $stokDisewa;
                            ?>

                            <tr>

                                <td>
                                    <?= $index + 1 ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $baris['nama_perlengkapan'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $baris['jenis_perlengkapan'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= (int) $baris['stok_total'] ?>
                                </td>

                                <td>
                                    <?= $stokTersedia ?>
                                </td>

                                <td>

                                    <?php if ($stokTersedia > 0): ?>

                                        <span class="status-tersedia">
                                            <?= htmlspecialchars(t('tersedia'), ENT_QUOTES, 'UTF-8') ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="status-habis">
                                            <?= htmlspecialchars(t('stok_habis'), ENT_QUOTES, 'UTF-8') ?>
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    Rp <?= number_format(
                                        (float) $baris['harga_perhari'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>

                                <td>

                                    <div class="actions">

                                        <a
                                            class="btn"
                                            href="edit_perlengkapan.php?id=<?= (int) $baris['id_perlengkapan'] ?>"
                                        >
                                            <?= htmlspecialchars(t('edit'), ENT_QUOTES, 'UTF-8') ?>
                                        </a>

                                        <a
                                            class="btn btn-danger"
                                            href="hapus_perlengkapan.php?id=<?= (int) $baris['id_perlengkapan'] ?>"
                                            onclick="return confirm('<?= htmlspecialchars(t('konfirmasi_hapus'), ENT_QUOTES, 'UTF-8') ?>')"
                                        >
                                            <?= htmlspecialchars(t('hapus'), ENT_QUOTES, 'UTF-8') ?>
                                        </a>

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

<!-- Menghapus parameter status dari URL setelah notifikasi ditampilkan -->
<script>
    if (window.location.search.includes('status=')) {
        const url = new URL(window.location.href);

        url.searchParams.delete('status');

        window.history.replaceState(
            {},
            document.title,
            url.pathname + url.search
        );
    }
</script>

</body>
</html>