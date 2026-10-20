<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
function check_login() {
    if (!isset($_SESSION['user'])) {
        header('Location: /inventory-app/public/login.php');
        exit;
    }
}

// Cek apakah user adalah admin
function check_admin() {
    check_login();
    if ($_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo 'Akses Ditolak: Hanya Admin yang boleh mengakses halaman ini.';
        exit;
    }
}