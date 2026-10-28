<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../auth/guard.php';
requireRol('cliente', 'admin');
require_once __DIR__ . '/../config/db.php';

$body = json_decode(file_get_contents('php://input'), true);
$idRutina = (int)($body['id_rutina'] ?? 0);

if (!$idRutina) {
    echo json_encode(['ok' => false, 'msg' => 'Rutina no válida.']);
    exit;
}

$db = getDB();
$idCliente = $_SESSION['id_usuario'];

try {
    // Verificar si ya existe un registro de hoy
    $check = $db->prepare("SELECT id_registro FROM REGISTRO_ENTRENAMIENTO WHERE id_cliente=? AND id_rutina=? AND fecha=CAST(GETDATE() AS DATE)");
    $check->execute([$idCliente, $idRutina]);
    $existe = $check->fetch();

    if ($existe) {
        $db->prepare("UPDATE REGISTRO_ENTRENAMIENTO SET completado=1, hora_registro=GETDATE() WHERE id_registro=?")
           ->execute([$existe['id_registro']]);
    } else {
        $db->prepare("INSERT INTO REGISTRO_ENTRENAMIENTO (id_cliente, id_rutina, completado) VALUES (?, ?, 1)")
           ->execute([$idCliente, $idRutina]);
    }

    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    error_log('[GYM] Error marcar completado: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'msg' => 'Error al registrar.']);
}
exit;