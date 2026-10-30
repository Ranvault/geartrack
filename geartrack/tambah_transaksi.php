<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

$error = '';

$idPenyewa = '';
$idPerlengkapan = '';
$jumlahDisewa = '1';
$tanggalSewa = date('Y-m-d');
$tanggalKembali = '';

try {
    // Mengambil data penyewa
    $stmtPenyewa = $pdo->query(
        "SELECT id_penyewa, nama_penyewa
         FROM penyewa
         ORDER BY nama_penyewa ASC" 
    );

    $dataPenyewa = $stmtPenyewa->fetchAll(PDO::FETCH_ASSOC);

    // Mengambil data perlengkapan
    $stmtPerlengkapan = $pdo->query(
        "SELECT id_perlengkapan, nama_perlengkapan,
                stok_total, stok_perawatan,
                stok_tidak_layak, harga_perhari
         FROM perlengkapan
         ORDER BY nama_perlengkapan ASC"
    );

    $dataPerlengkapan = $stmtPerlengkapan->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Data formulir gagal dimuat.');
}

// Memproses formulir ketika tombol simpan ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idPenyewa = filter_input(
        INPUT_POST,
        'id_penyewa',
        FILTER_VALIDATE_INT
    );

    $idPerlengkapan = filter_input(
        INPUT_POST,
        'id_perlengkapan',
        FILTER_VALIDATE_INT
    );

    $jumlahDisewa = filter_input(
        INPUT_POST,
        'jumlah_disewa',
        FILTER_VALIDATE_INT
    );

    $tanggalSewa = $_POST['tanggal_sewa'] ?? '';
    $tanggalKembali = $_POST['tanggal_rencana_kembali'] ?? '';

    // Memeriksa format tanggal
    $formatTanggalValid = static function ($tanggal) {
        if (!is_string($tanggal) || $tanggal === '') {
            return false;
        }

        $hasil = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $tanggal
        );

        return $hasil !== false
            && $hasil->format('Y-m-d') === $tanggal;
    };

    // Validasi data formulir
    if (
        !$idPenyewa ||
        !$idPerlengkapan ||
        !$jumlahDisewa ||
        $jumlahDisewa < 1
    ) {
        $error = 'Pilih penyewa, perlengkapan, dan jumlah yang valid.';

    } elseif (
        !$formatTanggalValid($tanggalSewa) ||
        !$formatTanggalValid($tanggalKembali) ||
        $tanggalKembali < $tanggalSewa
    ) {
        $error = 'Tanggal tidak valid. Tanggal kembali harus sama atau setelah tanggal sewa.';

    } else {
        try {
            // Memulai transaksi database
            $pdo->beginTransaction();

            // Memastikan penyewa tersedia
            $stmt = $pdo->prepare(
                "SELECT id_penyewa
                 FROM penyewa
                 WHERE id_penyewa = :id"
            );

            $stmt->execute([
                ':id' => $idPenyewa
            ]);

            if (!$stmt->fetch()) {
                throw new RuntimeException(
                    'Penyewa tidak ditemukan.'
                );
            }

            // Mengambil data dan memeriksa stok perlengkapan
            $stmt = $pdo->prepare(
                "SELECT stok_total, stok_perawatan,
                        stok_tidak_layak, harga_perhari
                 FROM perlengkapan
                 WHERE id_perlengkapan = :id
                 FOR UPDATE"
            );

            $stmt->execute([
                ':id' => $idPerlengkapan
            ]);

            $barang = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$barang) {
                throw new RuntimeException(
                    'Perlengkapan tidak ditemukan.'
                );
            }

            // Menghitung stok dasar yang layak disewakan
            $stokDasar = (int) $barang['stok_total']
                - (int) $barang['stok_perawatan']
                - (int) $barang['stok_tidak_layak'];

            // Menghitung jumlah barang yang sudah disewa
            // pada periode tanggal yang beririsan
            $stmtDipakai = $pdo->prepare(
                "SELECT COALESCE(SUM(dp.jumlah_disewa), 0)
                FROM detail_penyewaan dp
                JOIN transaksi t
                    ON dp.id_transaksi = t.id_transaksi
                WHERE dp.id_perlengkapan = :id_perlengkapan
                AND t.status_transaksi = 'Disewa'
                AND t.tanggal_sewa <= :tanggal_kembali
                AND t.tanggal_rencana_kembali >= :tanggal_sewa"
            );

            $stmtDipakai->execute([
                ':id_perlengkapan' => $idPerlengkapan,
                ':tanggal_kembali' => $tanggalKembali,
                ':tanggal_sewa' => $tanggalSewa
            ]);

            $stokSedangDisewa = (int) $stmtDipakai->fetchColumn();

            // Menghitung stok yang tersedia pada periode tersebut
            $stokTersedia = $stokDasar - $stokSedangDisewa;

            // Menolak transaksi jika stok tidak mencukupi
            if ($stokTersedia < 0 || $jumlahDisewa > $stokTersedia) {
                throw new RuntimeException(
                    'Stok tidak mencukupi pada periode tanggal penyewaan tersebut.'
                );
            }

            // Menghitung durasi sewa
            $mulai = new DateTimeImmutable($tanggalSewa);
            $kembali = new DateTimeImmutable($tanggalKembali);

            $jumlahHari = max(
                1,
                (int) $mulai->diff($kembali)->days
            );

            // Mengambil harga sewa per hari
            $hargaSewa = (float) $barang['harga_perhari'];

            // Menyimpan transaksi utama
            $stmt = $pdo->prepare(
                "INSERT INTO transaksi
                    (
                        id_penyewa,
                        id_admin,
                        tanggal_sewa,
                        tanggal_rencana_kembali,
                        tanggal_kembali_aktual,
                        status_transaksi
                    )
                 VALUES
                    (
                        :penyewa,
                        :admin,
                        :tanggal_sewa,
                        :tanggal_kembali,
                        NULL,
                        'Disewa'
                    )"
            );

            $stmt->execute([
                ':penyewa' => $idPenyewa,
                ':admin' => $_SESSION['id_admin'],
                ':tanggal_sewa' => $tanggalSewa,
                ':tanggal_kembali' => $tanggalKembali
            ]);

            // Mengambil ID transaksi yang baru dibuat
            $idTransaksi = (int) $pdo->lastInsertId();

            // Menyimpan detail perlengkapan yang disewa
            $stmt = $pdo->prepare(
                "INSERT INTO detail_penyewaan
                    (
                        id_transaksi,
                        id_perlengkapan,
                        jumlah_disewa,
                        harga_sewa
                    )
                 VALUES
                    (
                        :transaksi,
                        :perlengkapan,
                        :jumlah,
                        :harga
                    )"
            );

            $stmt->execute([
                ':transaksi' => $idTransaksi,
                ':perlengkapan' => $idPerlengkapan,
                ':jumlah' => $jumlahDisewa,
                ':harga' => $hargaSewa
            ]);

            // Menyelesaikan penyimpanan database
            $pdo->commit();

            // Kembali ke halaman transaksi
            header('Location: transaksi.php');
            exit;

        } catch (Throwable $e) {

            // Membatalkan penyimpanan jika terjadi kesalahan
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($e instanceof PDOException) {
                error_log($e->getMessage());

                $error = 'Transaksi gagal disimpan karena kesalahan database.';
            } else {
                $error = $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Buat Transaksi - GearTrack</title>

    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">

    <header class="header">
        <div>
            <h1>Buat Transaksi Penyewaan</h1>
            <p>Masukkan informasi penyewaan perlengkapan outdoor.</p>
        </div>

        <a href="transaksi.php" class="btn btn-secondary">
            Kembali
        </a>
    </header>

    <section class="card">

        <?php if ($error !== ''): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($dataPenyewa)): ?>

            <div class="alert alert-error">
                Belum ada data penyewa.
                Tambahkan penyewa terlebih dahulu.
            </div>

        <?php elseif (empty($dataPerlengkapan)): ?>

            <div class="alert alert-error">
                Belum ada data perlengkapan.
            </div>

        <?php else: ?>

            <form method="POST" action="tambah_transaksi.php">

                <!-- Pilihan penyewa -->
                <div class="form-group">
                    <label for="id_penyewa">Penyewa</label>

                    <select
                        id="id_penyewa"
                        name="id_penyewa"
                        required
                    >
                        <option value="">-- Pilih Penyewa --</option>

                        <?php foreach ($dataPenyewa as $penyewa): ?>

                            <option
                                value="<?= (int) $penyewa['id_penyewa'] ?>"
                                <?= (string) $idPenyewa === (string) $penyewa['id_penyewa'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars(
                                    $penyewa['nama_penyewa'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </option>

                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Pilihan perlengkapan -->
                <div class="form-group">
                    <label for="id_perlengkapan">Perlengkapan</label>

                    <select
                        id="id_perlengkapan"
                        name="id_perlengkapan"
                        required
                    >
                    <small id="infoStok">
                    Pilih tanggal sewa dan tanggal kembali untuk memeriksa stok.
                    </small>
                        <option value="">-- Pilih Perlengkapan --</option>

                        <?php foreach ($dataPerlengkapan as $barang): ?>

                            <?php
                            $stokTersedia = (int) $barang['stok_total']
                                - (int) $barang['stok_perawatan']
                                - (int) $barang['stok_tidak_layak'];
                            ?>

                            <option
                                value="<?= (int) $barang['id_perlengkapan'] ?>"
                                <?= (string) $idPerlengkapan === (string) $barang['id_perlengkapan'] ? 'selected' : '' ?>
                                <?= $stokTersedia < 1 ? 'disabled' : '' ?>
                            >
                                <?= htmlspecialchars(
                                    $barang['nama_perlengkapan'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                — Stok tersedia: <?= $stokTersedia ?>
                            </option>

                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Jumlah perlengkapan -->
                <div class="form-group">
                    <label for="jumlah_disewa">Jumlah Disewa</label>

                    <input
                        type="number"
                        id="jumlah_disewa"
                        name="jumlah_disewa"
                        min="1"
                        value="<?= htmlspecialchars(
                            (string) $jumlahDisewa,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        required
                    >
                </div>

                <!-- Tanggal sewa -->
                <div class="form-group">
                    <label for="tanggal_sewa">Tanggal Sewa</label>

                    <input
                        type="date"
                        id="tanggal_sewa"
                        name="tanggal_sewa"
                        value="<?= htmlspecialchars(
                            $tanggalSewa,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        required
                    >
                </div>

                <!-- Tanggal rencana kembali -->
                <div class="form-group">
                    <label for="tanggal_rencana_kembali">
                        Tanggal Rencana Kembali
                    </label>

                    <input
                        type="date"
                        id="tanggal_rencana_kembali"
                        name="tanggal_rencana_kembali"
                        value="<?= htmlspecialchars(
                            $tanggalKembali,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        required
                    >
                </div>

                <!-- Tombol simpan -->
                <button type="submit" class="btn">
                    Simpan Transaksi
                </button>

            </form>

        <?php endif; ?>

    </section>

</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tanggalSewa = document.querySelector('[name="tanggal_sewa"]');
    const tanggalKembali = document.querySelector('[name="tanggal_rencana_kembali"]');
    const pilihanPerlengkapan = document.getElementById('id_perlengkapan');
    const infoStok = document.getElementById('infoStok');

    async function periksaStok() {
        const mulai = tanggalSewa.value;
        const selesai = tanggalKembali.value;

        if (!mulai || !selesai || selesai < mulai) {
            infoStok.textContent =
                'Pilih tanggal sewa dan tanggal kembali yang valid.';
            return;
        }

        infoStok.textContent = 'Sedang memeriksa stok...';

        try {
            const url = new URL('cek_stok.php', window.location.href);
            url.searchParams.set('tanggal_sewa', mulai);
            url.searchParams.set('tanggal_kembali', selesai);

            const response = await fetch(url);
            const hasil = await response.json();

            if (!response.ok || !hasil.success) {
                throw new Error('Gagal memeriksa stok.');
            }

            const dataStok = new Map(
                hasil.data.map(barang => [
                    String(barang.id_perlengkapan),
                    Number(barang.stok_tersedia)
                ])
            );

            for (const opsi of pilihanPerlengkapan.options) {
                if (!opsi.value) continue;

                const stok = dataStok.get(opsi.value) ?? 0;
                const nama = opsi.dataset.nama;

                if (!nama) {
                    opsi.dataset.nama = opsi.textContent
                        .replace(/\s*[—-]\s*Stok tersedia:.*$/i, '')
                        .trim();
                }

                opsi.textContent =
                    `${opsi.dataset.nama} — Stok tersedia: ${stok}`;

                opsi.disabled = stok < 1;
            }

            if (
                pilihanPerlengkapan.selectedOptions.length &&
                pilihanPerlengkapan.selectedOptions[0].disabled
            ) {
                pilihanPerlengkapan.value = '';
            }

            infoStok.textContent =
                'Stok diperbarui sesuai tanggal penyewaan.';
        } catch (error) {
            infoStok.textContent =
                'Stok gagal diperiksa. Silakan coba lagi.';
            console.error(error);
        }
    }

    tanggalSewa.addEventListener('change', periksaStok);
    tanggalKembali.addEventListener('change', periksaStok);

    periksaStok();
});
</script>
</body>
</html>