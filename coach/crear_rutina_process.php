<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../auth/guard.php';
requireRol('coach', 'admin');
require_once __DIR__ . '/../config/db.php';

$body = json_decode(file_get_contents('php://input'), true);

$nombre     = trim($body['nombre'] ?? '');
$dia        = trim($body['dia']    ?? '');
$ejercicios = $body['ejercicios']  ?? [];

if (!$nombre || !$dia || empty($ejercicios)) {
    echo json_encode(['ok' => false, 'msg' => 'Datos incompletos.']);
    exit;
}

$db = getDB();
$idCoach = $_SESSION['id_usuario'];

try {
    $db->beginTransaction();

    $db->prepare("INSERT INTO RUTINA (id_coach, nombre, dia_semana) VALUES (?, ?, ?)")
       ->execute([$idCoach, htmlspecialchars($nombre), $dia]);

    $idRutina = $db->lastInsertId();

    $orden = 1;
    foreach ($ejercicios as $ej) {
        $db->prepare("
            INSERT INTO RUTINA_EJERCICIO (id_rutina, nombre_ejercicio, series, repeticiones, orden)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([
            $idRutina,
            htmlspecialchars($ej['nombre']),
            (int)$ej['series'],
            (int)$ej['repeticiones'],
            $orden++
        ]);
    }

    $db->commit();
    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    $db->rollBack();
    error_log('[GYM] Error crear rutina: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'msg' => 'Error al guardar la rutina.']);
}
exit;