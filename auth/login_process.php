<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) $body = $_POST;

$email    = $body['email']    ?? '';
$password = $body['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['ok' => false, 'msg' => 'Email y contraseña requeridos.']);
    exit;
}

require_once __DIR__ . '/../auth/AuthService.php';

$auth = new AuthService();
$ip   = $_SERVER['REMOTE_ADDR'] ?? '';

$resultado = $auth->login($email, $password, $ip);

if (!$resultado['ok']) {
    usleep(random_int(300000, 700000));
}

echo json_encode($resultado);
exit;