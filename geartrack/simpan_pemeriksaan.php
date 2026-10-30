<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: transaksi.php');
    exit;
}

$idTransaksi = filter_input(
    INPUT_POST,
    'id_transaksi',
    FILTER_VALIDATE_INT
);

$kondisiBarang = $_POST['kondisi'] ?? [];

if (
    !$idTransaksi ||
    $idTransaksi < 1 ||
    !is_array($kondisiBarang) ||
    empty($kondisiBarang)
) {
    exit('Data pemeriksaan tidak valid.');
}

$daftarKondisi = [
    'Layak',
    'Perlu Perawatan',
    'Tidak Layak'
];

try {
    $pdo->beginTransaction();

    // Pastikan transaksi sudah dikembalikan
    $stmt = $pdo->prepare(
        "SELECT id_transaksi, status_transaksi
         FROM transaksi
         WHERE id_transaksi = :id
         FOR UPDATE"
    );

    $stmt->execute([':id' => $idTransaksi]);
    $transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$transaksi ||
        $transaksi['status_transaksi'] !== 'Dikembalikan'
    ) {
        throw new RuntimeException(
            'Transaksi belum dikembalikan atau tidak ditemukan.'
        );
    }

    // Ambil semua detail barang dalam transaksi
    $stmt = $pdo->prepare(
        "SELECT id_detail, id_perlengkapan, jumlah_disewa
         FROM detail_penyewaan
         WHERE id_transaksi = :id"
    );

    $stmt->execute([':id' => $idTransaksi]);
    $detailBarang = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($detailBarang)) {
        throw new RuntimeException(
            'Tidak ada barang dalam transaksi ini.'
        );
    }

    // Pastikan seluruh detail mendapatkan kondisi yang valid
    $idDetailValid = [];

    foreach ($detailBarang as $detail) {
        $idDetail = (string) $detail['id_detail'];

        if (
            !array_key_exists($idDetail, $kondisiBarang) ||
            !is_string($kondisiBarang[$idDetail]) ||
            !in_array(
                $kondisiBarang[$idDetail],
                $daftarKondisi,
                true
            )
        ) {
            throw new RuntimeException(
                'Pilih kondisi untuk setiap barang.'
            );
        }

        $idDetailValid[] = $idDetail;
    }

    // Simpan hasil pemeriksaan dan perbarui stok
    foreach ($detailBarang as $detail) {
        $idDetail = (int) $detail['id_detail'];
        $idPerlengkapan = (int) $detail['id_perlengkapan'];
        $kondisi = $kondisiBarang[(string) $idDetail];

        // Kunci baris perlengkapan untuk mencegah perubahan bersamaan
        $stmt = $pdo->prepare(
            "SELECT stok_total, stok_perawatan, stok_tidak_layak
             FROM perlengkapan
             WHERE id_perlengkapan = :id
             FOR UPDATE"
        );

        $stmt->execute([':id' => $idPerlengkapan]);
        $stok = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$stok) {
            throw new RuntimeException(
                'Data perlengkapan tidak ditemukan.'
            );
        }

        // Cegah pemeriksaan ganda
        $stmt = $pdo->prepare(
            "SELECT id_pemeriksaan
             FROM pemeriksaan_kondisi
             WHERE id_detail = :id
             FOR UPDATE"
        );

        $stmt->execute([':id' => $idDetail]);

        if ($stmt->fetch()) {
            throw new RuntimeException(
                'Barang dalam transaksi ini sudah diperiksa sebelumnya.'
            );
        }

        // Simpan hasil pemeriksaan
        $stmt = $pdo->prepare(
            "INSERT INTO pemeriksaan_kondisi
                (id_detail, kondisi, tanggal_pemeriksaan)
             VALUES
                (:id_detail, :kondisi, :tanggal)"
        );

        $stmt->execute([
            ':id_detail' => $idDetail,
            ':kondisi' => $kondisi,
            ':tanggal' => date('Y-m-d')
        ]);

        // Perbarui kategori stok sesuai hasil pemeriksaan
        $stokPerawatan = (int) $stok['stok_perawatan'];
        $stokTidakLayak = (int) $stok['stok_tidak_layak'];
        $jumlah = (int) $detail['jumlah_disewa'];

        if ($kondisi === 'Perlu Perawatan') {
            $stokPerawatan += $jumlah;
        } elseif ($kondisi === 'Tidak Layak') {
            $stokTidakLayak += $jumlah;
        }

        $stmt = $pdo->prepare(
            "UPDATE perlengkapan
             SET stok_perawatan = :perawatan,
                 stok_tidak_layak = :tidak_layak
             WHERE id_perlengkapan = :id"
        );

        $stmt->execute([
            ':perawatan' => $stokPerawatan,
            ':tidak_layak' => $stokTidakLayak,
            ':id' => $idPerlengkapan
        ]);
    }

    $pdo->commit();

        header(
            'Location: detail_transaksi.php?id=' . $idTransaksi
        );
        exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($e instanceof PDOException) {
        error_log($e->getMessage());
        exit('Pemeriksaan gagal disimpan karena kesalahan database.');
    }

    exit(htmlspecialchars(
        $e->getMessage(),
        ENT_QUOTES,
        'UTF-8'
    ));
}
?>