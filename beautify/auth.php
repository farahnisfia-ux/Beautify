<?php
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /beautify/login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function requireAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: /beautify/login.php");
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function userName() {
    return $_SESSION['user_nama'] ?? '';
}

function userEmail() {
    return $_SESSION['user_email'] ?? '';
}

function userFoto() {
    return $_SESSION['user_foto'] ?? '';
}

function userId() {
    return (int)($_SESSION['user_id'] ?? 0);
}

function fotoSrc($foto) {
    if (!$foto) return '';
    $path = $_SERVER['DOCUMENT_ROOT'] . '/beautify/assets/img/profil/' . $foto;
    if (file_exists($path)) {
        return '/beautify/assets/img/profil/' . $foto;
    }
    return '';
}
