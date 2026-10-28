<?php
require_once __DIR__ . '/auth/guard.php';
if (($_SESSION['rol'] ?? '') === 'coach') {
    header('Location: /coach/panel.php');
    exit;
}
if (($_SESSION['rol'] ?? '') === 'cliente') {
    header('Location: /cliente/mi_rutina.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>GymForce — Dashboard</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { background:#080810; color:#e8e8f0; font-family:'Segoe UI',sans-serif; }
.topbar {
    background:#111118; border-bottom:1px solid #2a2a38;
    padding:16px 32px; display:flex; justify-content:space-between; align-items:center;
}
.logo { font-family:'Arial Black',sans-serif; font-size:18px; color:#fff; letter-spacing:2px; }
.logo span { color:#e63030; }
.user-info { display:flex; align-items:center; gap:16px; font-size:14px; color:#7070a0; }
.btn-logout {
    background:#e63030; color:#fff; border:none; padding:8px 16px;
    border-radius:4px; cursor:pointer; font-size:13px;
}
.content { padding:40px 32px; }
.welcome { font-size:28px; font-weight:700; margin-bottom:8px; }
.welcome span { color:#e63030; }
.cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; margin-top:32px; }
.card {
    background:#111118; border:1px solid #2a2a38;
    border-radius:8px; padding:24px;
}
.card .num { font-size:36px; font-weight:700; color:#e63030; }
.card .lbl { font-size:12px; color:#7070a0; margin-top:4px; letter-spacing:1px; text-transform:uppercase; }
</style>
</head>
<body>
<div class="topbar">
    <div class="logo">GYM<span>FORCE</span></div>
    <div class="user-info">
        Bienvenido, <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong>
        (<?= htmlspecialchars($_SESSION['rol']) ?>)
        <form method="POST" action="/auth/logout.php">
            <button class="btn-logout">Cerrar sesión</button>
        </form>
    </div>
</div>
<div class="content">
    <div class="welcome">Panel de control, <span><?= htmlspecialchars($_SESSION['nombre']) ?></span></div>
    <p style="color:#7070a0;margin-top:8px">Sistema de gestión GymForce</p>
    <div class="cards">
        <div class="card"><div class="num">1</div><div class="lbl">Usuarios</div></div>
        <div class="card"><div class="num">4</div><div class="lbl">Roles</div></div>
        <div class="card"><div class="num">0</div><div class="lbl">Clases activas</div></div>
        <div class="card"><div class="num">0</div><div class="lbl">Membresías</div></div>
    </div>
</div>
</body>
</html>