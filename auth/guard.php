<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthService.php';

ini_set('session.cookie_httponly', 1);
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['token'])) {
    header('Location: /login.php?expired=1');
    exit;
}

function requireRol(string ...$rolesPermitidos): void {
    $rolActual = $_SESSION['rol'] ?? '';
    if (!in_array($rolActual, $rolesPermitidos, true)) {
        http_response_code(403);
        header('Location: /acceso_denegado.php');
        exit;
    }
}