<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id < 1) {
    header('Location: index.php?status=id_tidak_valid');
    exit;
}

try {
    // Ambil data lama
    $sql = "SELECT *
            FROM perlengkapan
            WHERE id_perlengkapan = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    $perlengkapan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$perlengkapan) {
        header('Location: index.php?status=data_tidak_ditemukan');
        exit;
    }

    $errors = [];

    // Proses saat formulir dikirim
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama = trim($_POST['nama_perlengkapan'] ?? '');
        $jenis = trim($_POST['jenis_perlengkapan'] ?? '');
        $stok = $_POST['stok_total'] ?? '';
        $perawatan = $_POST['stok_perawatan'] ?? '';
        $tidakLayak = $_POST['stok_tidak_layak'] ?? '';
        $harga = $_POST['harga_perhari'] ?? '';

        if ($nama === '' || $jenis === '') {
            $errors[] = 'Nama dan jenis perlengkapan wajib diisi.';
        }

        foreach ([
            'Stok total' => $stok,
            'Stok perawatan' => $perawatan,
            'Stok tidak layak' => $tidakLayak
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
            ((int) $perawatan + (int) $tidakLayak > (int) $stok)
        ) {
            $errors[] = 'Stok perawatan dan stok tidak layak tidak boleh melebihi stok total.';
        }

        if (empty($errors)) {
            $sql = "UPDATE perlengkapan
                    SET nama_perlengkapan = :nama,
                        jenis_perlengkapan = :jenis,
                        stok_total = :stok,
                        stok_perawatan = :perawatan,
                        stok_tidak_layak = :tidak_layak,
                        harga_perhari = :harga
                    WHERE id_perlengkapan = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nama' => $nama,
                ':jenis' => $jenis,
                ':stok' => $stok,
                ':perawatan' => $perawatan,
                ':tidak_layak' => $tidakLayak,
                ':harga' => $harga,
                ':id' => $id
            ]);

            header('Location: index.php?status=edit_berhasil');
            exit;
        }

        // Pertahankan nilai yang dikirim jika validasi gagal
        $perlengkapan['nama_perlengkapan'] = $nama;
        $perlengkapan['jenis_perlengkapan'] = $jenis;
        $perlengkapan['stok_total'] = $stok;
        $perlengkapan['stok_perawatan'] = $perawatan;
        $perlengkapan['stok_tidak_layak'] = $tidakLayak;
        $perlengkapan['harga_perhari'] = $harga;
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Terjadi kesalahan saat memproses data perlengkapan.');
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Perlengkapan - GearTrack</title>
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
        <h2>Edit Perlengkapan</h2>
        <p>Perbarui informasi perlengkapan yang tersimpan.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Data belum dapat diperbarui:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li>
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST"
              action="edit_perlengkapan.php?id=<?= (int) $id ?>">

            <div class="form-group">
                <label for="nama_perlengkapan">Nama Perlengkapan</label>
                <input
                    type="text"
                    id="nama_perlengkapan"
                    name="nama_perlengkapan"
                    value="<?= htmlspecialchars($perlengkapan['nama_perlengkapan'], ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="jenis_perlengkapan">Jenis Perlengkapan</label>
                <input
                    type="text"
                    id="jenis_perlengkapan"
                    name="jenis_perlengkapan"
                    value="<?= htmlspecialchars($perlengkapan['jenis_perlengkapan'], ENT_QUOTES, 'UTF-8') ?>"
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
                    value="<?= htmlspecialchars((string) $perlengkapan['stok_total'], ENT_QUOTES, 'UTF-8') ?>"
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
                    value="<?= htmlspecialchars((string) $perlengkapan['stok_perawatan'], ENT_QUOTES, 'UTF-8') ?>"
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
                    value="<?= htmlspecialchars((string) $perlengkapan['stok_tidak_layak'], ENT_QUOTES, 'UTF-8') ?>"
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
                    value="<?= htmlspecialchars((string) $perlengkapan['harga_perhari'], ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <button class="btn" type="submit">
                Simpan Perubahan
            </button>

            <a class="btn btn-secondary" href="index.php">
                Batal
            </a>

        </form>
    </section>

</div>

</body>
</html>