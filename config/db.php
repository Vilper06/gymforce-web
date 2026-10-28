<?php
// Configuración de conexión. Los valores reales se toman de variables de entorno del servidor.
define('DB_SERVER', getenv('GYM_DB_SERVER') ?: 'localhost\\SQLEXPRESS');
define('DB_NAME',   getenv('GYM_DB_NAME') ?: 'GYM');
define('SESSION_LIFETIME', 7200);

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = "sqlsrv:Server=" . DB_SERVER . ";Database=" . DB_NAME . ";TrustServerCertificate=yes";

    try {
        $pdo = new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        die(json_encode(['ok' => false, 'msg' => 'Error de conexión con la base de datos.']));
    }

    return $pdo;
}
