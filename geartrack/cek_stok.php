<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');

// Ambil tanggal dari permintaan JavaScript
$tanggalSewa = $_GET['tanggal_sewa'] ?? '';
$tanggalKembali = $_GET['tanggal_kembali'] ?? '';

// Validasi format tanggal
$formatTanggal = '/^\d{4}-\d{2}-\d{2}$/';

if (
    !preg_match($formatTanggal, $tanggalSewa) ||
    !preg_match($formatTanggal, $tanggalKembali) ||
    $tanggalKembali < $tanggalSewa
) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Tanggal penyewaan tidak valid.'
    ]);

    exit;
}

try {
    $sql = "
        SELECT
            p.id_perlengkapan,
            p.nama_perlengkapan,
            p.stok_total,
            p.stok_perawatan,
            p.stok_tidak_layak,
            COALESCE(sewa.jumlah_disewa, 0) AS stok_sedang_disewa
        FROM perlengkapan p
        LEFT JOIN (
            SELECT
                dp.id_perlengkapan,
                SUM(dp.jumlah_disewa) AS jumlah_disewa
            FROM detail_penyewaan dp
            JOIN transaksi t
                ON dp.id_transaksi = t.id_transaksi
            WHERE t.status_transaksi = 'Disewa'
              AND t.tanggal_sewa <= :tanggal_kembali
              AND t.tanggal_rencana_kembali >= :tanggal_sewa
            GROUP BY dp.id_perlengkapan
        ) sewa
            ON p.id_perlengkapan = sewa.id_perlengkapan
        ORDER BY p.nama_perlengkapan ASC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':tanggal_kembali' => $tanggalKembali,
        ':tanggal_sewa' => $tanggalSewa
    ]);

    $dataPerlengkapan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $hasil = [];

    foreach ($dataPerlengkapan as $barang) {
        $stokDasar =
            (int) $barang['stok_total']
            - (int) $barang['stok_perawatan']
            - (int) $barang['stok_tidak_layak'];

        $stokTersedia = max(
            0,
            $stokDasar - (int) $barang['stok_sedang_disewa']
        );

        $hasil[] = [
            'id_perlengkapan' => (int) $barang['id_perlengkapan'],
            'nama_perlengkapan' => $barang['nama_perlengkapan'],
            'stok_tersedia' => $stokTersedia
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $hasil
    ]);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal memeriksa ketersediaan stok.'
    ]);
}