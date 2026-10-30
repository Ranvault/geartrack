<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit;
}
?>
