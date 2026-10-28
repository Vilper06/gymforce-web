<?php
ini_set('session.cookie_httponly', 1);
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['token'])) {
    header('Location: /dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GymForce — Iniciar Sesión</title>
<style>
:root {
  --black:    #080810;
  --surface:  #111118;
  --card:     #18181f;
  --border:   #2a2a38;
  --red:      #e63030;
  --red-glow: rgba(230,48,48,.35);
  --orange:   #ff6b1a;
  --text:     #e8e8f0;
  --muted:    #7070a0;
  --input-bg: #0e0e16;
  --radius:   6px;
  --ff-display: 'Arial Black', 'Impact', sans-serif;
  --ff-body:    'Segoe UI', system-ui, sans-serif;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  min-height: 100vh;
  background: var(--black);
  font-family: var(--ff-body);
  color: var(--text);
  display: grid;
  grid-template-columns: 1fr 480px;
  overflow: hidden;
}
.hero {
  position: relative;
  background: #0a0a12;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 60px;
  overflow: hidden;
}
.hero::before {
  content: '';
  position: absolute; inset: 0;
  background: repeating-linear-gradient(-45deg, transparent, transparent 60px, rgba(230,48,48,.03) 60px, rgba(230,48,48,.03) 61px);
  animation: slide 20s linear infinite;
}
@keyframes slide { from { background-position: 0 0; } to { background-position: 200px 200px; } }
.hero::after {
  content: '';
  position: absolute;
  top: -200px; left: -200px;
  width: 700px; height: 700px;
  background: radial-gradient(circle, rgba(230,48,48,.12) 0%, transparent 70%);
  animation: pulse 4s ease-in-out infinite alternate;
}
@keyframes pulse { from { opacity: .6; } to { opacity: 1; } }
.hero-stat {
  position: absolute;
  top: 60px; left: 60px;
  z-index: 2;
  display: flex; gap: 40px;
}
.stat-block .num {
  font-family: var(--ff-display);
  font-size: 36px;
  color: var(--red);
  line-height: 1;
  text-shadow: 0 0 20px var(--red-glow);
}
.stat-block .lbl {
  font-size: 11px; color: var(--muted);
  letter-spacing: 2px; text-transform: uppercase;
  margin-top: 4px;
}
.hero-content { position: relative; z-index: 2; }
.hero-eyebrow { font-size: 11px; color: var(--red); letter-spacing: 4px; text-transform: uppercase; margin-bottom: 16px; }
.hero-title {
  font-family: var(--ff-display);
  font-size: clamp(42px, 5vw, 72px);
  line-height: .9;
  text-transform: uppercase;
  color: #fff;
}
.hero-title span { display: block; color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,.25); }
.hero-sub { margin-top: 20px; font-size: 14px; color: var(--muted); max-width: 340px; line-height: 1.7; }
.progress-bars { margin-top: 40px; display: flex; flex-direction: column; gap: 10px; }
.pb-row { display: flex; align-items: center; gap: 12px; }
.pb-label { font-size: 11px; color: var(--muted); width: 90px; }
.pb-track { flex: 1; height: 3px; background: rgba(255,255,255,.07); border-radius: 2px; overflow: hidden; }
.pb-fill { height: 100%; border-radius: 2px; background: linear-gradient(90deg, var(--red), var(--orange)); animation: fillBar 2s cubic-bezier(.4,0,.2,1) forwards; width: 0; }
@keyframes fillBar { to { width: var(--w); } }
.panel {
  background: var(--surface);
  border-left: 1px solid var(--border);
  display: flex; flex-direction: column; justify-content: center;
  padding: 60px 48px;
  position: relative; overflow: hidden;
}
.panel::before {
  content: ''; position: absolute; bottom: -120px; right: -120px;
  width: 400px; height: 400px;
  background: radial-gradient(circle, rgba(230,48,48,.08) 0%, transparent 70%);
  pointer-events: none;
}
.logo { display: flex; align-items: center; gap: 12px; margin-bottom: 48px; }
.logo-icon {
  width: 36px; height: 36px; background: var(--red); border-radius: 8px;
  display: flex; align-items: center; justify-content: center; font-size: 18px;
  box-shadow: 0 0 20px var(--red-glow);
}
.logo-text { font-family: var(--ff-display); font-size: 20px; letter-spacing: 2px; color: #fff; text-transform: uppercase; }
.logo-text span { color: var(--red); }
.form-title { font-size: 26px; font-weight: 700; color: #fff; margin-bottom: 6px; }
.form-sub { font-size: 13px; color: var(--muted); margin-bottom: 36px; }
.field { margin-bottom: 20px; }
.field label { display: block; font-size: 11px; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px; }
.input-wrap { position: relative; }
.input-wrap .icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 15px; color: var(--muted); pointer-events: none; }
.input-wrap input {
  width: 100%; background: var(--input-bg); border: 1px solid var(--border);
  border-radius: var(--radius); color: var(--text); font-size: 14px;
  padding: 12px 14px 12px 42px; outline: none;
  transition: border-color .2s, box-shadow .2s;
}
.input-wrap input:focus { border-color: var(--red); box-shadow: 0 0 0 3px var(--red-glow); }
.input-wrap input::placeholder { color: #3a3a5a; }
.btn-eye { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--muted); cursor: pointer; font-size: 16px; }
.form-opts { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; }
.remember { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: var(--muted); }
.forgot { font-size: 13px; color: var(--red); text-decoration: none; }
.btn-login {
  width: 100%; padding: 14px; background: var(--red); color: #fff;
  font-family: var(--ff-display); font-size: 14px; letter-spacing: 2px;
  text-transform: uppercase; border: none; border-radius: var(--radius);
  cursor: pointer; transition: background .2s, box-shadow .2s;
}
.btn-login:hover { background: #ff3333; box-shadow: 0 6px 24px var(--red-glow); }
.btn-login:disabled { background: #3a1a1a; color: #6a3030; cursor: not-allowed; }
.spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; vertical-align: middle; margin-right: 6px; }
@keyframes spin { to { transform: rotate(360deg); } }
.alert { margin-top: 16px; padding: 12px 14px; border-radius: var(--radius); font-size: 13px; display: none; align-items: center; gap: 10px; }
.alert.show { display: flex; }
.alert.error   { background: rgba(230,48,48,.12); border: 1px solid rgba(230,48,48,.3); color: #ff8080; }
.alert.success { background: rgba(40,200,100,.1);  border: 1px solid rgba(40,200,100,.3); color: #5de89e; }
.divider { margin: 28px 0; display: flex; align-items: center; gap: 12px; color: var(--muted); font-size: 12px; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
.register-link { text-align: center; font-size: 13px; color: var(--muted); }
.register-link a { color: var(--red); text-decoration: none; font-weight: 600; }
.field-error { font-size: 11px; color: #ff6060; margin-top: 5px; display: none; }
.field.invalid .input-wrap input { border-color: #e63030; }
.field.invalid .field-error { display: block; }
@media (max-width: 860px) { body { grid-template-columns: 1fr; } .hero { display: none; } .panel { padding: 40px 28px; } }
</style>
</head>
<body>

<section class="hero">
  <div class="hero-stat">
    <div class="stat-block">
      <div class="num" id="cnt-miembros">0</div>
      <div class="lbl">Miembros</div>
    </div>
    <div class="stat-block">
      <div class="num" id="cnt-clases">0</div>
      <div class="lbl">Clases / semana</div>
    </div>
    <div class="stat-block">
      <div class="num" id="cnt-entrenadores">0</div>
      <div class="lbl">Entrenadores</div>
    </div>
  </div>
  <div class="hero-content">
    <div class="hero-eyebrow">Bienvenido de regreso</div>
    <h1 class="hero-title">GYM<span>FORCE</span>PRO</h1>
    <p class="hero-sub">Gestiona membresías, clases, pagos y control de acceso desde un solo panel.</p>
    <div class="progress-bars">
      <div class="pb-row"><span class="pb-label">Fuerza</span><div class="pb-track"><div class="pb-fill" style="--w:88%"></div></div></div>
      <div class="pb-row"><span class="pb-label">Resistencia</span><div class="pb-track"><div class="pb-fill" style="--w:74%;animation-delay:.2s"></div></div></div>
      <div class="pb-row"><span class="pb-label">Rendimiento</span><div class="pb-track"><div class="pb-fill" style="--w:95%;animation-delay:.4s"></div></div></div>
    </div>
  </div>
</section>

<section class="panel">
  <div class="logo">
    <div class="logo-icon">G</div>
    <div class="logo-text">GYM<span>FORCE</span></div>
  </div>
  <div class="form-title">Acceder al sistema</div>
  <p class="form-sub">Ingresa tus credenciales para continuar</p>
  <div id="loginForm">
    <div class="field" id="fieldEmail">
      <label for="email">Correo electrónico</label>
      <div class="input-wrap">
        <span class="icon">@</span>
        <input type="email" id="email" placeholder="usuario@gym.com" autocomplete="username" maxlength="120">
      </div>
      <div class="field-error" id="errEmail">Ingresa un correo válido.</div>
    </div>
    <div class="field" id="fieldPassword">
      <label for="password">Contraseña</label>
      <div class="input-wrap">
        <span class="icon">*</span>
        <input type="password" id="password" placeholder="••••••••••" autocomplete="current-password" maxlength="128">
        <button class="btn-eye" type="button" id="btnEye">O</button>
      </div>
      <div class="field-error">Ingresa tu contraseña.</div>
    </div>
    <div class="form-opts">
      <label class="remember"><input type="checkbox"> Recordarme</label>
      <a href="#" class="forgot">¿Olvidaste tu contraseña?</a>
    </div>
    <button class="btn-login" id="btnLogin" type="button">Iniciar sesión</button>
    <div class="alert" id="alertMsg">
      <span id="alertIcon">!</span>
      <span id="alertText"></span>
    </div>
  </div>
  <div class="divider">o</div>
  <div class="register-link">¿Eres nuevo? <a href="/registro.php">Crear cuenta</a></div>
</section>

<script>
function animCount(el, target, duration=1800) {
  let start = null;
  const step = ts => {
    if (!start) start = ts;
    const p = Math.min((ts - start) / duration, 1);
    el.textContent = Math.floor(p * target).toLocaleString();
    if (p < 1) requestAnimationFrame(step);
  };
  requestAnimationFrame(step);
}
animCount(document.getElementById('cnt-miembros'), 1248);
animCount(document.getElementById('cnt-clases'), 32, 1200);
animCount(document.getElementById('cnt-entrenadores'), 18, 1400);

document.getElementById('btnEye').addEventListener('click', function() {
  const p = document.getElementById('password');
  const show = p.type === 'password';
  p.type = show ? 'text' : 'password';
  this.textContent = show ? 'X' : 'O';
});

function showAlert(msg, tipo='error') {
  const el = document.getElementById('alertMsg');
  el.className = `alert show ${tipo}`;
  document.getElementById('alertIcon').textContent = tipo === 'error' ? '!' : '✓';
  document.getElementById('alertText').textContent = msg;
}

function setLoading(on) {
  const btn = document.getElementById('btnLogin');
  btn.disabled = on;
  btn.innerHTML = on ? '<span class="spinner"></span>Verificando...' : 'Iniciar sesión';
}

document.getElementById('btnLogin').addEventListener('click', handleLogin);
document.getElementById('password').addEventListener('keydown', e => { if(e.key==='Enter') handleLogin(); });
document.getElementById('email').addEventListener('keydown', e => { if(e.key==='Enter') handleLogin(); });

async function handleLogin() {
  const email    = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;
  const emailOk  = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  document.getElementById('fieldEmail').classList.toggle('invalid', !emailOk);
  if (!emailOk || !password) return;
  setLoading(true);
  try {
    const res  = await fetch('/auth/login_process.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
      credentials: 'same-origin',
    });
    const data = await res.json();
    if (data.ok) {
      showAlert('¡Acceso concedido! Redirigiendo…', 'success');
      setTimeout(() => window.location.href = data.redirect || '/dashboard.php', 800);
    } else {
      showAlert(data.msg || 'Credenciales incorrectas.');
    }
  } catch(err) {
    showAlert('Error de conexión.');
  } finally {
    setLoading(false);
  }
}
</script>
</body>
</html>