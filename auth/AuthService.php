<?php
require_once __DIR__ . '/../config/db.php';

class AuthService {

    private PDO $db;
    private const MAX_INTENTOS = 5;
    private const BCRYPT_COST  = 12;

    public function __construct() {
        $this->db = getDB();
    }

    public function login(string $email, string $password, string $ip = ''): array {

        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'msg' => 'Credenciales inválidas.'];
        }

        // 1. Buscar usuario + credenciales (sin rol todavía)
        $stmt = $this->db->prepare("
            SELECT u.id_usuario, u.nombre, u.activo,
                   c.hash_password, c.intentos_fail, c.bloqueado
            FROM USUARIO u
            INNER JOIN CREDENCIALES c ON c.id_usuario = u.id_usuario
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user)              return ['ok' => false, 'msg' => 'Credenciales inválidas.'];
        if ($user['bloqueado'])  return ['ok' => false, 'msg' => 'Cuenta bloqueada.'];
        if (!$user['activo'])    return ['ok' => false, 'msg' => 'Cuenta inactiva.'];

        if (!password_verify($password, $user['hash_password'])) {
            $nuevos   = $user['intentos_fail'] + 1;
            $bloquear = ($nuevos >= self::MAX_INTENTOS) ? 1 : 0;
            $this->db->prepare("UPDATE CREDENCIALES SET intentos_fail=?, bloqueado=? WHERE id_usuario=?")
                     ->execute([$nuevos, $bloquear, $user['id_usuario']]);
            return ['ok' => false, 'msg' => 'Credenciales inválidas.'];
        }

        // 2. Consultar el perfil/rol vía la tabla puente USUARIO_PERFIL
        $stmtPerfil = $this->db->prepare("
            SELECT r.nombre_rol
            FROM USUARIO_PERFIL up
            INNER JOIN ROL r ON r.id_rol = up.id_rol
            WHERE up.id_usuario = ? AND up.estatus = 1
        ");
        $stmtPerfil->execute([$user['id_usuario']]);
        $perfil = $stmtPerfil->fetch();
        $rolUsuario = $perfil ? $perfil['nombre_rol'] : 'cliente';

        $this->db->prepare("UPDATE CREDENCIALES SET intentos_fail=0, ultimo_login=GETDATE() WHERE id_usuario=?")
                 ->execute([$user['id_usuario']]);

        $token  = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);

        $this->db->prepare("INSERT INTO SESION (id_usuario, token, ip_origen, fecha_expira) VALUES (?,?,?,?)")
                 ->execute([$user['id_usuario'], $token, $ip, $expira]);

        ini_set('session.cookie_httponly', 1);
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_regenerate_id(true);
        $_SESSION['id_usuario'] = $user['id_usuario'];
        $_SESSION['nombre']     = $user['nombre'];
        $_SESSION['rol']        = $rolUsuario;
        $_SESSION['token']      = $token;

        $redirect = match($rolUsuario) {
            'coach'   => '/coach/panel.php',
            'cliente' => '/cliente/mi_rutina.php',
            'admin'   => '/dashboard.php',
            default   => '/dashboard.php',
        };

        return [
            'ok'      => true,
            'token'   => $token,
            'nombre'  => $user['nombre'],
            'rol'     => $rolUsuario,
            'redirect'=> $redirect,
        ];
    }

    public function logout(): void {
        if (isset($_SESSION['token'])) {
            $this->db->prepare("UPDATE SESION SET activa=0 WHERE token=?")
                     ->execute([$_SESSION['token']]);
        }
        session_destroy();
    }
}