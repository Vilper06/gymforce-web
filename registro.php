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
<title>GymForce — Registro</title>
<style>
:root {
  --black:    #080810;
  --surface:  #111118;
  --border:   #2a2a38;
  --red:      #e63030;
  --red-glow: rgba(230,48,48,.35);
  --text:     #e8e8f0;
  --muted:    #7070a0;
  --input-bg: #0e0e16;
  --radius:   6px;
  --ff-display: 'Arial Black', sans-serif;
  --ff-body:    'Segoe UI', system-ui, sans-serif;
}
*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
body {
  min-height:100vh; background:var(--black);
  font-family:var(--ff-body); color:var(--text);
  display:flex; align-items:center; justify-content:center; padding:40px 20px;
}
.card {
  background:var(--surface); border:1px solid var(--border);
  border-radius:12px; padding:48px 40px; width:100%; max-width:480px;
  position:relative; overflow:hidden;
}
.logo { display:flex; align-items:center; gap:10px; margin-bottom:32px; }
.logo-icon {
  width:32px; height:32px; background:var(--red); border-radius:6px;
  display:flex; align-items:center; justify-content:center; font-size:16px;
  box-shadow:0 0 16px var(--red-glow);
}
.logo-text { font-family:var(--ff-display); font-size:18px; letter-spacing:2px; color:#fff; text-transform:uppercase; }
.logo-text span { color:var(--red); }
.form-title { font-size:22px; font-weight:700; color:#fff; margin-bottom:4px; }
.form-sub { font-size:13px; color:var(--muted); margin-bottom:28px; }
.field { margin-bottom:16px; }
.field label { display:block; font-size:11px; color:var(--muted); letter-spacing:1.5px; text-transform:uppercase; margin-bottom:6px; }
.input-wrap { position:relative; }
.input-wrap .icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:14px; color:var(--muted); pointer-events:none; }
.input-wrap input { width:100%; background:var(--input-bg); border:1px solid var(--border); border-radius:var(--radius); color:var(--text); font-size:14px; padding:11px 14px 11px 38px; outline:none; transition:border-color .2s, box-shadow .2s; font-family:var(--ff-body); }
.input-wrap input:focus { border-color:var(--red); box-shadow:0 0 0 3px var(--red-glow); }
.input-wrap input::placeholder { color:#3a3a5a; }
.btn-eye { position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--muted); cursor:pointer; font-size:15px; }
.role-select { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:20px; }
.role-card { background:var(--input-bg); border:2px solid var(--border); border-radius:8px; padding:16px; text-align:center; cursor:pointer; transition:all .2s; }
.role-card .icon { font-size:28px; margin-bottom:6px; }
.role-card .title { font-size:13px; font-weight:700; color:#fff; }
.role-card .sub { font-size:10px; color:var(--muted); margin-top:2px; }
.role-card.selected { border-color:var(--red); background:rgba(230,48,48,.08); box-shadow:0 0 16px var(--red-glow); }
.strength-wrap { margin-top:8px; }
.strength-bars { display:flex; gap:4px; }
.sb { flex:1; height:3px; border-radius:2px; background:var(--border); transition:background .3s; }
.strength-txt { font-size:10px; color:var(--muted); margin-top:4px; text-align:right; }
.field-error { font-size:11px; color:#ff6060; margin-top:4px; display:none; }
.field.invalid .input-wrap input { border-color:#e63030; }
.field.invalid .field-error { display:block; }
.btn-register { width:100%; padding:13px; background:var(--red); color:#fff; font-family:var(--ff-display); font-size:13px; letter-spacing:2px; text-transform:uppercase; border:none; border-radius:var(--radius); cursor:pointer; margin-top:8px; transition:background .2s, box-shadow .2s; }
.btn-register:hover { background:#ff3333; box-shadow:0 6px 24px var(--red-glow); }
.btn-register:disabled { background:#3a1a1a; color:#6a3030; cursor:not-allowed; }
.alert { margin-top:14px; padding:11px 14px; border-radius:var(--radius); font-size:13px; display:none; align-items:center; gap:8px; }
.alert.show { display:flex; }
.alert.error   { background:rgba(230,48,48,.12); border:1px solid rgba(230,48,48,.3); color:#ff8080; }
.alert.success { background:rgba(40,200,100,.1);  border:1px solid rgba(40,200,100,.3); color:#5de89e; }
.spinner { display:inline-block; width:14px; height:14px; border:2px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; vertical-align:middle; margin-right:6px; }
@keyframes spin { to { transform:rotate(360deg); } }
.login-link { text-align:center; font-size:13px; color:var(--muted); margin-top:20px; }
.login-link a { color:var(--red); text-decoration:none; font-weight:600; }
.security-info { background:#0a0a14; border:1px solid #1a1a2e; border-radius:var(--radius); padding:12px 14px; margin-bottom:20px; font-size:11px; color:#5050a0; line-height:1.8; }
.security-info span { color:#4040c0; }
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-icon">G</div>
    <div class="logo-text">GYM<span>FORCE</span></div>
  </div>

  <div class="form-title">Crear cuenta</div>
  <p class="form-sub">Elige tu tipo de cuenta y únete</p>

  <div class="role-select">
    <div class="role-card selected" id="roleCliente" data-rol="cliente">
      <div class="icon">C</div>
      <div class="title">Cliente</div>
      <div class="sub">Quiero entrenar</div>
    </div>
    <div class="role-card" id="roleCoach" data-rol="coach">
      <div class="icon">C</div>
      <div class="title">Coach</div>
      <div class="sub">Quiero entrenar a otros</div>
    </div>
  </div>

  <div class="security-info">
    <span>Contraseña cifrada con bcrypt</span> — Tu contraseña nunca se guarda en texto plano.
  </div>

  <div class="field" id="fieldNombre">
    <label>Nombre completo</label>
    <div class="input-wrap">
      <span class="icon">N</span>
      <input type="text" id="nombre" placeholder="Francisco Valadez" maxlength="80">
    </div>
    <div class="field-error">Ingresa tu nombre.</div>
  </div>

  <div class="field" id="fieldEmail">
    <label>Correo electrónico</label>
    <div class="input-wrap">
      <span class="icon">@</span>
      <input type="email" id="email" placeholder="usuario@gym.com" maxlength="120">
    </div>
    <div class="field-error">Ingresa un correo válido.</div>
  </div>

  <div class="field" id="fieldPassword">
    <label>Contraseña</label>
    <div class="input-wrap">
      <span class="icon">*</span>
      <input type="password" id="password" placeholder="Mínimo 8 caracteres" maxlength="128">
      <button class="btn-eye" type="button" id="btnEye">O</button>
    </div>
    <div class="strength-wrap">
      <div class="strength-bars">
        <div class="sb" id="sb1"></div>
        <div class="sb" id="sb2"></div>
        <div class="sb" id="sb3"></div>
        <div class="sb" id="sb4"></div>
      </div>
      <div class="strength-txt" id="strengthTxt">Escribe una contraseña</div>
    </div>
    <div class="field-error">8+ chars, mayúscula, número y símbolo.</div>
  </div>

  <div class="field" id="fieldConfirm">
    <label>Confirmar contraseña</label>
    <div class="input-wrap">
      <span class="icon">K</span>
      <input type="password" id="confirm" placeholder="Repite tu contraseña" maxlength="128">
    </div>
    <div class="field-error">Las contraseñas no coinciden.</div>
  </div>

  <button class="btn-register" id="btnRegister" type="button">Crear cuenta</button>

  <div class="alert" id="alertMsg">
    <span id="alertIcon">!</span>
    <span id="alertText"></span>
  </div>

  <div class="login-link">¿Ya tienes cuenta? <a href="/login.php">Iniciar sesión</a></div>
</div>

<script>
let rolSeleccionado = 'cliente';

document.getElementById('roleCliente').addEventListener('click', function() {
  rolSeleccionado = 'cliente';
  this.classList.add('selected');
  document.getElementById('roleCoach').classList.remove('selected');
});
document.getElementById('roleCoach').addEventListener('click', function() {
  rolSeleccionado = 'coach';
  this.classList.add('selected');
  document.getElementById('roleCliente').classList.remove('selected');
});

document.getElementById('btnEye').addEventListener('click', function() {
  const p = document.getElementById('password');
  const show = p.type === 'password';
  p.type = show ? 'text' : 'password';
  this.textContent = show ? 'X' : 'O';
});

document.getElementById('password').addEventListener('input', function() {
  const p = this.value;
  let score = 0;
  if (p.length >= 8)   score++;
  if (/[A-Z]/.test(p)) score++;
  if (/[0-9]/.test(p)) score++;
  if (/[\W_]/.test(p)) score++;
  const colors = ['#e63030','#ff6b1a','#ffd166','#06d6a0'];
  const labels = ['Muy débil','Débil','Media','Fuerte'];
  ['sb1','sb2','sb3','sb4'].forEach((id, i) => {
    document.getElementById(id).style.background = i < score ? colors[score-1] : 'var(--border)';
  });
  document.getElementById('strengthTxt').textContent = p.length ? labels[score-1] || 'Muy débil' : 'Escribe una contraseña';
  document.getElementById('strengthTxt').style.color = p.length ? colors[score-1] : 'var(--muted)';
});

function showAlert(msg, tipo='error') {
  const el = document.getElementById('alertMsg');
  el.className = `alert show ${tipo}`;
  document.getElementById('alertIcon').textContent = tipo === 'error' ? '!' : '✓';
  document.getElementById('alertText').textContent = msg;
}
function setLoading(on) {
  const btn = document.getElementById('btnRegister');
  btn.disabled = on;
  btn.innerHTML = on ? '<span class="spinner"></span>Creando cuenta...' : 'Crear cuenta';
}

document.getElementById('btnRegister').addEventListener('click', async function() {
  const nombre   = document.getElementById('nombre').value.trim();
  const email    = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;
  const confirm  = document.getElementById('confirm').value;

  let ok = true;
  document.getElementById('fieldNombre').classList.toggle('invalid', !nombre);
  if (!nombre) ok = false;

  const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  document.getElementById('fieldEmail').classList.toggle('invalid', !emailOk);
  if (!emailOk) ok = false;

  const passOk = password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[\W_]/.test(password);
  document.getElementById('fieldPassword').classList.toggle('invalid', !passOk);
  if (!passOk) ok = false;

  const confirmOk = password === confirm;
  document.getElementById('fieldConfirm').classList.toggle('invalid', !confirmOk);
  if (!confirmOk) ok = false;

  if (!ok) return;

  setLoading(true);

  try {
    const res  = await fetch('/auth/registro_process.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ nombre, email, password, rol: rolSeleccionado }),
      credentials: 'same-origin',
    });
    const data = await res.json();

    if (data.ok) {
      showAlert('¡Cuenta creada! Redirigiendo al login...', 'success');
      setTimeout(() => window.location.href = '/login.php', 1500);
    } else {
      showAlert(data.msg || 'Error al registrar.');
    }
  } catch(err) {
    showAlert('Error de conexión.');
  } finally {
    setLoading(false);
  }
});
</script>
</body>
</html>