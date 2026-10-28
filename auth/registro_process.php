<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) $body = $_POST;

$nombre   = trim($body['nombre']   ?? '');
$email    = trim($body['email']    ?? '');
$password =      $body['password'] ?? '';
$rol      = trim($body['rol']      ?? 'cliente');

if (!$nombre || !$email || !$password) {
    echo json_encode(['ok' => false, 'msg' => 'Todos los campos son requeridos.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'msg' => 'Email inválido.']);
    exit;
}

if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[\W_]/', $password)) {
    echo json_encode(['ok' => false, 'msg' => 'La contraseña no cumple los requisitos.']);
    exit;
}

$idRol = match($rol) {
    'coach'   => 2,
    'cliente' => 3,
    default   => 3,
};

require_once __DIR__ . '/../config/db.php';

$db = getDB();

$check = $db->prepare("SELECT id_usuario FROM USUARIO WHERE email = ?");
$check->execute([$email]);
if ($check->fetch()) {
    echo json_encode(['ok' => false, 'msg' => 'El email ya está registrado.']);
    exit;
}

try {
    $db->beginTransaction();

    // Insertar en USUARIO (catálogo general, sin id_rol directo)
    $db->prepare("INSERT INTO USUARIO (nombre, email, id_rol, activo) VALUES (?, ?, ?, 1)")
       ->execute([htmlspecialchars($nombre), strtolower($email), $idRol]);

    $idUsuario = $db->lastInsertId();

    // Insertar en la tabla puente USUARIO_PERFIL
    $db->prepare("INSERT INTO USUARIO_PERFIL (id_usuario, id_rol, estatus) VALUES (?, ?, 1)")
       ->execute([$idUsuario, $idRol]);

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $db->prepare("INSERT INTO CREDENCIALES (id_usuario, hash_password) VALUES (?, ?)")
       ->execute([$idUsuario, $hash]);

    $db->commit();

    echo json_encode(['ok' => true, 'msg' => 'Registro exitoso.']);

} catch (Exception $e) {
    $db->rollBack();
    error_log('[GYM] Error registro: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'msg' => 'Error al registrar. Intenta de nuevo.']);
}
exit;