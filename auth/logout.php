<?php
require_once __DIR__ . '/../config/db.php';

ini_set('session.cookie_httponly', 1);
if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['token'])) {
    $db = getDB();
    $db->prepare("UPDATE SESION SET activa=0 WHERE token=?")
       ->execute([$_SESSION['token']]);
}

session_destroy();
header('Location: /login.php');
exit;