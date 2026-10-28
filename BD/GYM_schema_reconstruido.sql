/* =====================================================================
   GYM - Script de creacion de base de datos (RECONSTRUIDO)
   Motor: Microsoft SQL Server (probado contra SQL Server Express,
          instancia local SQLEXPRESS)

   IMPORTANTE:
   En la carpeta BD/ del proyecto solo existian dos consultas sueltas
   (SQLQuery1.sql y SQLQuery2.sql), no un script de creacion completo.
   Este script fue reconstruido a partir de las tablas, columnas y
   relaciones que el codigo PHP usa realmente (config/db.php,
   auth/*.php, coach/*.php, cliente/*.php). Antes de ejecutarlo en un
   entorno nuevo, revisa que los tipos de dato coincidan con tu BD
   original si ya existe.
   ===================================================================== */

USE master;
GO

IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = 'GYM')
BEGIN
    CREATE DATABASE GYM;
END
GO

USE GYM;
GO

/* ---------------------------------------------------------------------
   1. ROL  -  Catalogo de roles del sistema (admin, coach, cliente, etc.)
   --------------------------------------------------------------------- */
CREATE TABLE ROL (
    id_rol      INT IDENTITY(1,1) PRIMARY KEY,
    nombre_rol  VARCHAR(30) NOT NULL UNIQUE
);
GO

/* ---------------------------------------------------------------------
   2. USUARIO  -  Catalogo general de usuarios del sistema
   --------------------------------------------------------------------- */
CREATE TABLE USUARIO (
    id_usuario  INT IDENTITY(1,1) PRIMARY KEY,
    nombre      VARCHAR(80)  NOT NULL,
    email       VARCHAR(120) NOT NULL UNIQUE,
    id_rol      INT NOT NULL,
    activo      BIT NOT NULL DEFAULT 1,
    CONSTRAINT FK_USUARIO_ROL FOREIGN KEY (id_rol) REFERENCES ROL(id_rol)
);
GO

/* ---------------------------------------------------------------------
   3. USUARIO_PERFIL  -  Tabla puente usuario <-> rol
      Permite, a futuro, mas de un perfil activo por usuario sin
      modificar el esquema.
   --------------------------------------------------------------------- */
CREATE TABLE USUARIO_PERFIL (
    id_usuario  INT NOT NULL,
    id_rol      INT NOT NULL,
    estatus     BIT NOT NULL DEFAULT 1,
    CONSTRAINT PK_USUARIO_PERFIL PRIMARY KEY (id_usuario, id_rol),
    CONSTRAINT FK_USUARIO_PERFIL_USUARIO FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario),
    CONSTRAINT FK_USUARIO_PERFIL_ROL     FOREIGN KEY (id_rol)     REFERENCES ROL(id_rol)
);
GO

/* ---------------------------------------------------------------------
   4. CREDENCIALES  -  Datos de autenticacion (1:1 con USUARIO)
   --------------------------------------------------------------------- */
CREATE TABLE CREDENCIALES (
    id_usuario     INT PRIMARY KEY,
    hash_password  VARCHAR(255) NOT NULL,
    intentos_fail  INT NOT NULL DEFAULT 0,
    bloqueado      BIT NOT NULL DEFAULT 0,
    ultimo_login   DATETIME NULL,
    CONSTRAINT FK_CREDENCIALES_USUARIO FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario)
);
GO

/* ---------------------------------------------------------------------
   5. SESION  -  Sesiones/tokens activos por usuario
   --------------------------------------------------------------------- */
CREATE TABLE SESION (
    id_sesion     INT IDENTITY(1,1) PRIMARY KEY,
    id_usuario    INT NOT NULL,
    token         VARCHAR(64) NOT NULL UNIQUE,
    ip_origen     VARCHAR(45) NULL,
    fecha_expira  DATETIME NOT NULL,
    activa        BIT NOT NULL DEFAULT 1,
    CONSTRAINT FK_SESION_USUARIO FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario)
);
GO

/* ---------------------------------------------------------------------
   6. RUTINA  -  Rutina de entrenamiento creada por un coach
   --------------------------------------------------------------------- */
CREATE TABLE RUTINA (
    id_rutina   INT IDENTITY(1,1) PRIMARY KEY,
    id_coach    INT NOT NULL,
    nombre      VARCHAR(100) NOT NULL,
    dia_semana  VARCHAR(15) NOT NULL,
    CONSTRAINT FK_RUTINA_COACH FOREIGN KEY (id_coach) REFERENCES USUARIO(id_usuario)
);
GO

/* ---------------------------------------------------------------------
   7. RUTINA_EJERCICIO  -  Ejercicios que componen una rutina
   --------------------------------------------------------------------- */
CREATE TABLE RUTINA_EJERCICIO (
    id_rutina_ejercicio  INT IDENTITY(1,1) PRIMARY KEY,
    id_rutina            INT NOT NULL,
    nombre_ejercicio     VARCHAR(100) NOT NULL,
    series               INT NOT NULL,
    repeticiones         INT NOT NULL,
    descanso_segundos    INT NOT NULL DEFAULT 60,
    orden                INT NOT NULL,
    CONSTRAINT FK_RUTEJ_RUTINA FOREIGN KEY (id_rutina) REFERENCES RUTINA(id_rutina)
);
GO

/* ---------------------------------------------------------------------
   8. ASIGNACION_RUTINA  -  Relacion rutina <-> cliente
      activa = 1 marca la asignacion vigente para ese dia/cliente.
   --------------------------------------------------------------------- */
CREATE TABLE ASIGNACION_RUTINA (
    id_asignacion  INT IDENTITY(1,1) PRIMARY KEY,
    id_rutina      INT NOT NULL,
    id_cliente     INT NOT NULL,
    activa         BIT NOT NULL DEFAULT 1,
    CONSTRAINT FK_ASIGRUT_RUTINA  FOREIGN KEY (id_rutina)  REFERENCES RUTINA(id_rutina),
    CONSTRAINT FK_ASIGRUT_CLIENTE FOREIGN KEY (id_cliente) REFERENCES USUARIO(id_usuario)
);
GO

/* ---------------------------------------------------------------------
   9. REGISTRO_ENTRENAMIENTO  -  Marca si un cliente completo su
      rutina en una fecha dada (uno por cliente/rutina/dia).
   --------------------------------------------------------------------- */
CREATE TABLE REGISTRO_ENTRENAMIENTO (
    id_registro    INT IDENTITY(1,1) PRIMARY KEY,
    id_cliente     INT NOT NULL,
    id_rutina      INT NOT NULL,
    fecha          DATE NOT NULL DEFAULT CAST(GETDATE() AS DATE),
    completado     BIT NOT NULL DEFAULT 0,
    hora_registro  DATETIME NOT NULL DEFAULT GETDATE(),
    CONSTRAINT FK_REGENT_CLIENTE FOREIGN KEY (id_cliente) REFERENCES USUARIO(id_usuario),
    CONSTRAINT FK_REGENT_RUTINA  FOREIGN KEY (id_rutina)  REFERENCES RUTINA(id_rutina)
);
GO

/* ---------------------------------------------------------------------
   10. HORARIO_GYM  -  Horario de apertura/cierre por dia de la semana
   --------------------------------------------------------------------- */
CREATE TABLE HORARIO_GYM (
    id_horario     INT IDENTITY(1,1) PRIMARY KEY,
    dia_semana     VARCHAR(15) NOT NULL UNIQUE,
    hora_apertura  TIME NULL,
    hora_cierre    TIME NULL,
    es_descanso    BIT NOT NULL DEFAULT 0
);
GO

/* ---------------------------------------------------------------------
   11. MAQUINA  -  Catalogo de maquinas del gimnasio
   --------------------------------------------------------------------- */
CREATE TABLE MAQUINA (
    id_maquina         INT IDENTITY(1,1) PRIMARY KEY,
    nombre             VARCHAR(100) NOT NULL,
    musculo_trabajado  VARCHAR(60)  NOT NULL,
    instrucciones      VARCHAR(500) NULL
);
GO

/* ---------------------------------------------------------------------
   Datos base sugeridos (catalogos minimos para que la app funcione)
   --------------------------------------------------------------------- */
INSERT INTO ROL (nombre_rol) VALUES ('admin'), ('coach'), ('cliente');
GO

INSERT INTO HORARIO_GYM (dia_semana, hora_apertura, hora_cierre, es_descanso) VALUES
('Lunes',     '06:00', '22:00', 0),
('Martes',    '06:00', '22:00', 0),
('Miercoles', '06:00', '22:00', 0),
('Jueves',    '06:00', '22:00', 0),
('Viernes',   '06:00', '22:00', 0),
('Sabado',    '08:00', '14:00', 0),
('Domingo',   NULL,    NULL,    1);
GO
