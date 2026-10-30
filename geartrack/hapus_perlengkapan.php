<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id < 1) {
    header('Location: index.php?status=id_tidak_valid');
    exit;
}

try {
    $sql = "DELETE FROM perlengkapan
            WHERE id_perlengkapan = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() > 0) {
        header('Location: index.php?status=hapus_berhasil');
    } else {
        header('Location: index.php?status=data_tidak_ditemukan');
    }

    exit;
} catch (PDOException $e) {
    error_log($e->getMessage());
    header('Location: index.php?status=hapus_gagal');
    exit;
}
