<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/bahasa.php';

$namaPenyewa = '';
$noTelepon = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaPenyewa = trim($_POST['nama_penyewa'] ?? '');
    $noTelepon = trim($_POST['no_telepon'] ?? '');

    if ($namaPenyewa === '' || $noTelepon === '') {
        $error = 'nama_telepon_wajib';
    } elseif (strlen($namaPenyewa) > 100 || strlen($noTelepon) > 100) {
        $error = 'batas_karakter_penyewa';
    } else {
        try {
            $sql = "INSERT INTO penyewa (nama_penyewa, no_telepon)
                    VALUES (:nama, :telepon)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nama' => $namaPenyewa,
                ':telepon' => $noTelepon
            ]);

            header('Location: penyewa.php');
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'gagal_simpan_penyewa';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['bahasa'] ?? 'id', ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars(t('tambah_penyewa'), ENT_QUOTES, 'UTF-8') ?>
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
                <?= htmlspecialchars(t('tambah_penyewa'), ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <p>
                <?= htmlspecialchars(t('deskripsi_form_penyewa'), ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>
    </header>

    <!-- Form Tambah Penyewa -->
    <section class="card">

        <?php if ($error !== ''): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars(t($error), ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="tambah_penyewa.php">

            <div class="form-group">
                <label for="nama_penyewa">
                    <?= htmlspecialchars(t('nama_penyewa'), ENT_QUOTES, 'UTF-8') ?>
                </label>

                <input
                    type="text"
                    id="nama_penyewa"
                    name="nama_penyewa"
                    maxlength="100"
                    value="<?= htmlspecialchars($namaPenyewa, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="no_telepon">
                    <?= htmlspecialchars(t('no_telepon'), ENT_QUOTES, 'UTF-8') ?>
                </label>

                <input
                    type="tel"
                    id="no_telepon"
                    name="no_telepon"
                    maxlength="100"
                    value="<?= htmlspecialchars($noTelepon, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <button type="submit" class="btn">
                <?= htmlspecialchars(t('simpan_penyewa'), ENT_QUOTES, 'UTF-8') ?>
            </button>

            <a href="penyewa.php" class="btn btn-secondary">
                <?= htmlspecialchars(t('batal'), ENT_QUOTES, 'UTF-8') ?>
            </a>

        </form>

    </section>

</div>

</body>
</html>