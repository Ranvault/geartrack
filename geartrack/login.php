
<?php
session_start();

require_once __DIR__ . '/config/database.php';

// Jika sudah login, arahkan ke dashboard
if (isset($_SESSION['id_admin'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        try {
            $sql = "SELECT id_admin, username, nama_admin, password_hash
                    FROM admin
                    WHERE username = :username
                    LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':username' => $username]);

            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (
                $admin &&
                password_verify($password, $admin['password_hash'])
            ) {
                session_regenerate_id(true);

                $_SESSION['id_admin'] = $admin['id_admin'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['nama_admin'] = $admin['nama_admin'];

                header('Location: index.php');
                exit;
            }

            $error = 'Username atau password salah.';

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - GearTrack</title>
    <link rel="stylesheet" href="assets/style.css">

    <style>
        .login-container {
            width: min(420px, 92%);
            margin: 70px auto;
        }

        .login-container h1 {
            color: #183b32;
            margin-bottom: 8px;
        }

        .login-container .subtitle {
            color: #687782;
            margin-bottom: 28px;
        }

        .login-container .btn {
            width: 100%;
            margin-top: 8px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <section class="card">
        <h1>GearTrack</h1>
        <p class="subtitle">Login Administrator</p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                    autocomplete="username"
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button class="btn" type="submit">Login</button>
        </form>
    </section>
</div>

</body>
</html>