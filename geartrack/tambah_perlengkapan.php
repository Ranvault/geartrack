<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

$errors = [];

$nama = '';
$jenis = '';
$stok = '';
$stok_perawatan = '';
$stok_tidak_layak = '';
$harga = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_perlengkapan'] ?? '');
    $jenis = trim($_POST['jenis_perlengkapan'] ?? '');
    $stok = $_POST['stok_total'] ?? '';
    $stok_perawatan = $_POST['stok_perawatan'] ?? '';
    $stok_tidak_layak = $_POST['stok_tidak_layak'] ?? '';
    $harga = $_POST['harga_perhari'] ?? '';

    if ($nama === '' || $jenis === '') {
        $errors[] = 'Nama dan jenis perlengkapan wajib diisi.';
    }

    foreach ([
        'Stok total' => $stok,
        'Stok perawatan' => $stok_perawatan,
        'Stok tidak layak' => $stok_tidak_layak
    ] as $label => $nilai) {
        if (
            filter_var($nilai, FILTER_VALIDATE_INT) === false ||
            (int) $nilai < 0
        ) {
            $errors[] = "$label harus berupa bilangan bulat minimal 0.";
        }
    }

    if (!is_numeric($harga) || (float) $harga < 0) {
        $errors[] = 'Harga per hari harus berupa angka minimal 0.';
    }

    if (
        empty($errors) &&
        (
            (int) $stok_perawatan + (int) $stok_tidak_layak >
            (int) $stok
        )
    ) {
        $errors[] = 'Jumlah stok perawatan dan stok tidak layak tidak boleh melebihi stok total.';
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO perlengkapan
                (nama_perlengkapan, jenis_perlengkapan, stok_total,
                 stok_perawatan, stok_tidak_layak, harga_perhari)
                VALUES
                (:nama, :jenis, :stok, :perawatan, :tidak_layak, :harga)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nama' => $nama,
                ':jenis' => $jenis,
                ':stok' => $stok,
                ':perawatan' => $stok_perawatan,
                ':tidak_layak' => $stok_tidak_layak,
                ':harga' => $harga
            ]);

            header('Location: index.php?status=berhasil');
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Data gagal disimpan. Periksa konfigurasi dan struktur database.';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Perlengkapan - GearTrack</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">

    <header class="header">
        <div>
            <h1>GearTrack</h1>
            <p>Sistem Manajemen Inventaris Perlengkapan Outdoor</p>
        </div>

        <a class="btn btn-secondary" href="index.php">
            Kembali ke Dashboard
        </a>
    </header>

    <section class="card">
        <h2>Tambah Perlengkapan</h2>
        <p>Masukkan informasi perlengkapan yang akan ditambahkan.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Data belum dapat disimpan:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li>
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="tambah_perlengkapan.php">

            <div class="form-group">
                <label for="nama_perlengkapan">Nama Perlengkapan</label>
                <input
                    type="text"
                    id="nama_perlengkapan"
                    name="nama_perlengkapan"
                    value="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="jenis_perlengkapan">Jenis Perlengkapan</label>
                <input
                    type="text"
                    id="jenis_perlengkapan"
                    name="jenis_perlengkapan"
                    value="<?= htmlspecialchars($jenis, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="stok_total">Stok Total</label>
                <input
                    type="number"
                    id="stok_total"
                    name="stok_total"
                    min="0"
                    step="1"
                    value="<?= htmlspecialchars((string) $stok, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="stok_perawatan">Stok Perawatan</label>
                <input
                    type="number"
                    id="stok_perawatan"
                    name="stok_perawatan"
                    min="0"
                    step="1"
                    value="<?= htmlspecialchars((string) $stok_perawatan, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="stok_tidak_layak">Stok Tidak Layak</label>
                <input
                    type="number"
                    id="stok_tidak_layak"
                    name="stok_tidak_layak"
                    min="0"
                    step="1"
                    value="<?= htmlspecialchars((string) $stok_tidak_layak, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="harga_perhari">Harga Sewa per Hari (Rp)</label>
                <input
                    type="number"
                    id="harga_perhari"
                    name="harga_perhari"
                    min="0"
                    step="any"
                    value="<?= htmlspecialchars((string) $harga, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <button class="btn" type="submit">Simpan Perlengkapan</button>

            <a class="btn btn-secondary" href="index.php">
                Batal
            </a>

        </form>
    </section>

</div>

</body>
</html>