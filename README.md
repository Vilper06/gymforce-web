# GymForce: gestión de gimnasio con roles

Proyecto final de **Programación Web** (Ingeniería en Informática, IPN-UPIICSA, junio 2026). Proyecto de equipo de 5 integrantes; mi parte fue la construcción de las páginas (HTML y CSS) y la configuración del servidor web IIS en Windows.

## Qué hace
Aplicación web cliente-servidor para un gimnasio con control de acceso por rol:
- **Coach:** consulta horario y máquinas, crea rutinas y las asigna a clientes (`coach/`).
- **Cliente:** ve la rutina del día y marca su cumplimiento (`cliente/`).
- **Registro e inicio de sesión** (`login.php`, `registro.php`, `auth/`).

## Stack
HTML, CSS y JavaScript (`fetch` con respuestas JSON) · React 18 (vista de la rutina del día del cliente) · PHP 8 con PDO · SQL Server Express · IIS con FastCGI.

## Seguridad aplicada
- Contraseñas con bcrypt (`password_hash` / `password_verify`).
- Bloqueo de la cuenta tras 5 intentos fallidos y mensaje genérico de error.
- Consultas preparadas contra inyección SQL.
- Regeneración del ID de sesión al autenticar y cookie `httponly`.
- Rol validado en el servidor en cada endpoint (`auth/guard.php`), no solo ocultando botones.
- `web.config`: IIS solo acepta los verbos GET y POST.

## Base de datos
`BD/GYM_schema_reconstruido.sql` crea las tablas (roles, usuarios, credenciales, sesiones, rutinas, máquinas). El servidor y la base se configuran con las variables de entorno `GYM_DB_SERVER` y `GYM_DB_NAME`.
