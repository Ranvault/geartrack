<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/bahasa.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id < 1) {
    header('Location: penyewa.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id_penyewa, nama_penyewa, no_telepon
     FROM penyewa
     WHERE id_penyewa = :id"
);
$stmt->execute([':id' => $id]);
$penyewa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$penyewa) {
    header('Location: penyewa.php');
    exit;
}

$namaPenyewa = $penyewa['nama_penyewa'];
$noTelepon = $penyewa['no_telepon'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaPenyewa = trim($_POST['nama_penyewa'] ?? '');
    $noTelepon = trim($_POST['no_telepon'] ?? '');

    if ($namaPenyewa === '' || $noTelepon === '') {
        $error = t('nama_telepon_wajib');
    } elseif (
        strlen($namaPenyewa) > 100 ||
        strlen($noTelepon) > 100
    ) {
        $error = t('batas_karakter_penyewa');
    } else {
        try {
            $sql = "UPDATE penyewa
                    SET nama_penyewa = :nama,
                        no_telepon = :telepon
                    WHERE id_penyewa = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nama' => $namaPenyewa,
                ':telepon' => $noTelepon,
                ':id' => $id
            ]);

            header('Location: penyewa.php');
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = t('gagal_perbarui_penyewa');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['bahasa'] ?? 'id', ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('edit_penyewa') ?> - GearTrack</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">
    <header class="header">
        <div>
            <h1><?= t('edit_penyewa') ?></h1>
            <p><?= t('deskripsi_edit_penyewa') ?></p>
        </div>
    </header>

    <section class="card">
        <?php if ($error !== ''): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit_penyewa.php?id=<?= (int) $id ?>">
            <div class="form-group">
                <label for="nama_penyewa"><?= t('nama_penyewa') ?></label>
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
                <label for="no_telepon"><?= t('no_telepon') ?></label>
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
                <?= t('simpan_perubahan') ?>
            </button>

            <a href="penyewa.php" class="btn btn-secondary">
                <?= t('batal') ?>
            </a>
        </form>
    </section>
</div>

</body>
</html>