<?php
require_once __DIR__ . '/config/database.php';

$username = 'admin';
$nama_admin = 'Administrator';
$password = '12345678';

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO admin (username, nama_admin, password_hash)
        VALUES (:username, :nama_admin, :password_hash)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':username' => $username,
    ':nama_admin' => $nama_admin,
    ':password_hash' => $password_hash
]);

echo "Akun admin berhasil dibuat.";
?>
