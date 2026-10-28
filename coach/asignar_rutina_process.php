<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../auth/guard.php';
requireRol('coach', 'admin');
require_once __DIR__ . '/../config/db.php';

$body = json_decode(file_get_contents('php://input'), true);

$idRutina  = (int)($body['id_rutina']  ?? 0);
$idCliente = (int)($body['id_cliente'] ?? 0);

if (!$idRutina || !$idCliente) {
    echo json_encode(['ok' => false, 'msg' => 'Datos incompletos.']);
    exit;
}

$db = getDB();

// Verificar que la rutina pertenece a este coach
$check = $db->prepare("SELECT id_rutina FROM RUTINA WHERE id_rutina = ? AND id_coach = ?");
$check->execute([$idRutina, $_SESSION['id_usuario']]);
if (!$check->fetch()) {
    echo json_encode(['ok' => false, 'msg' => 'Rutina no válida.']);
    exit;
}

try {
    // Desactivar asignaciones previas de ese cliente para ese mismo día (evitar duplicados de día)
    $db->prepare("
        UPDATE ASIGNACION_RUTINA SET activa = 0
        WHERE id_cliente = ? AND id_rutina IN (
            SELECT id_rutina FROM RUTINA WHERE dia_semana = (SELECT dia_semana FROM RUTINA WHERE id_rutina = ?)
        )
    ")->execute([$idCliente, $idRutina]);

    $db->prepare("INSERT INTO ASIGNACION_RUTINA (id_rutina, id_cliente) VALUES (?, ?)")
       ->execute([$idRutina, $idCliente]);

    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    error_log('[GYM] Error asignar rutina: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'msg' => 'Error al asignar.']);
}
exit;