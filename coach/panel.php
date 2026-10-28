<?php
require_once __DIR__ . '/../auth/guard.php';
requireRol('coach', 'admin');
require_once __DIR__ . '/../config/db.php';

$db = getDB();
$idCoach = $_SESSION['id_usuario'];

// Horario del gym
$horario = $db->query("SELECT * FROM HORARIO_GYM ORDER BY 
    CASE dia_semana 
        WHEN 'Lunes' THEN 1 WHEN 'Martes' THEN 2 WHEN 'Miercoles' THEN 3
        WHEN 'Jueves' THEN 4 WHEN 'Viernes' THEN 5 WHEN 'Sabado' THEN 6 ELSE 7 END")->fetchAll();

// Máquinas
$maquinas = $db->query("SELECT * FROM MAQUINA ORDER BY nombre")->fetchAll();

// Rutinas creadas por este coach
$rutinas = $db->prepare("SELECT * FROM RUTINA WHERE id_coach = ? ORDER BY 
    CASE dia_semana 
        WHEN 'Lunes' THEN 1 WHEN 'Martes' THEN 2 WHEN 'Miercoles' THEN 3
        WHEN 'Jueves' THEN 4 WHEN 'Viernes' THEN 5 WHEN 'Sabado' THEN 6 ELSE 7 END");
$rutinas->execute([$idCoach]);
$rutinas = $rutinas->fetchAll();

// Clientes disponibles para asignar
$clientes = $db->query("SELECT id_usuario, nombre, email FROM USUARIO WHERE id_rol = 3 AND activo = 1 ORDER BY nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GymForce — Panel Coach</title>
<style>
:root {
  --black:#080810; --surface:#111118; --card:#15151d; --border:#2a2a38;
  --red:#e63030; --red-glow:rgba(230,48,48,.35); --orange:#ff6b1a;
  --green:#06d6a0; --text:#e8e8f0; --muted:#7070a0; --input-bg:#0e0e16; --radius:8px;
  --ff-display:'Arial Black',sans-serif; --ff-body:'Segoe UI',system-ui,sans-serif;
}
* { margin:0; padding:0; box-sizing:border-box; }
body { background:var(--black); color:var(--text); font-family:var(--ff-body); }
.topbar { background:var(--surface); border-bottom:1px solid var(--border); padding:16px 28px; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:10; }
.logo { font-family:var(--ff-display); font-size:18px; color:#fff; letter-spacing:2px; }
.logo span { color:var(--red); }
.user-info { display:flex; align-items:center; gap:14px; font-size:13px; color:var(--muted); }
.badge-rol { background:rgba(230,48,48,.15); color:#ff8080; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; }
.btn-logout { background:var(--red); color:#fff; border:none; padding:8px 16px; border-radius:6px; cursor:pointer; font-size:12px; font-weight:600; }

.tabs { display:flex; gap:4px; padding:20px 28px 0; border-bottom:1px solid var(--border); }
.tab { padding:12px 20px; cursor:pointer; color:var(--muted); font-size:13px; font-weight:600; border-bottom:3px solid transparent; transition:.2s; }
.tab.active { color:#fff; border-color:var(--red); }
.tab:hover { color:#fff; }

.content { padding:28px; }
.tab-content { display:none; }
.tab-content.active { display:block; }

.section-title { font-size:20px; font-weight:700; margin-bottom:4px; color:#fff; }
.section-sub { font-size:13px; color:var(--muted); margin-bottom:20px; }

/* Horario */
.schedule-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; }
.day-card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:16px; text-align:center; }
.day-card.today { border-color:var(--red); box-shadow:0 0 16px var(--red-glow); }
.day-card .day-name { font-size:13px; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:1px; }
.day-card .day-hours { font-size:12px; color:var(--muted); margin-top:8px; }
.day-card .day-status { font-size:11px; margin-top:6px; padding:3px 8px; border-radius:12px; display:inline-block; }
.status-open { background:rgba(6,214,160,.15); color:var(--green); }
.status-closed { background:rgba(230,48,48,.15); color:#ff8080; }

/* Máquinas */
.machines-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:16px; }
.machine-card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:18px; }
.machine-card .m-name { font-size:15px; font-weight:700; color:#fff; margin-bottom:4px; }
.machine-card .m-muscle { font-size:11px; color:var(--orange); text-transform:uppercase; letter-spacing:1px; margin-bottom:10px; }
.machine-card .m-desc { font-size:12px; color:var(--muted); line-height:1.6; }

/* Rutinas */
.btn-primary { background:var(--red); color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-size:13px; font-weight:700; letter-spacing:.5px; }
.btn-primary:hover { background:#ff3333; }
.routine-card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:18px; margin-bottom:14px; }
.routine-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
.routine-name { font-size:15px; font-weight:700; color:#fff; }
.routine-day { font-size:11px; background:rgba(230,48,48,.15); color:#ff8080; padding:3px 10px; border-radius:20px; }
.ejercicio-row { display:flex; justify-content:space-between; padding:8px 0; border-top:1px solid var(--border); font-size:12px; color:var(--muted); }
.ejercicio-row b { color:#fff; }

/* Modal */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:100; align-items:center; justify-content:center; }
.modal-overlay.show { display:flex; }
.modal-box { background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:28px; width:90%; max-width:480px; max-height:85vh; overflow-y:auto; }
.modal-title { font-size:18px; font-weight:700; color:#fff; margin-bottom:16px; }
.field { margin-bottom:14px; }
.field label { display:block; font-size:11px; color:var(--muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px; }
.field input, .field select, .field textarea {
  width:100%; background:var(--input-bg); border:1px solid var(--border); border-radius:6px;
  color:var(--text); padding:10px 12px; font-size:13px; outline:none; font-family:var(--ff-body);
}
.field input:focus, .field select:focus { border-color:var(--red); }
.ejercicio-input-row { display:grid; grid-template-columns:2fr 1fr 1fr; gap:8px; margin-bottom:8px; }
.btn-add-ejercicio { background:var(--input-bg); border:1px dashed var(--border); color:var(--muted); padding:8px; border-radius:6px; cursor:pointer; width:100%; font-size:12px; margin-top:6px; }
.modal-actions { display:flex; gap:10px; margin-top:20px; }
.btn-secondary { background:var(--input-bg); color:var(--muted); border:1px solid var(--border); padding:10px 20px; border-radius:6px; cursor:pointer; font-size:13px; flex:1; }
.alert { margin-top:14px; padding:10px 14px; border-radius:6px; font-size:12px; display:none; }
.alert.show { display:block; }
.alert.error { background:rgba(230,48,48,.12); color:#ff8080; }
.alert.success { background:rgba(40,200,100,.1); color:#5de89e; }
</style>
</head>
<body>

<div class="topbar">
  <div class="logo">GYM<span>FORCE</span></div>
  <div class="user-info">
    <span class="badge-rol">Coach</span>
    Hola, <strong style="color:#fff"><?= htmlspecialchars($_SESSION['nombre']) ?></strong>
    <form method="POST" action="/auth/logout.php"><button class="btn-logout">Salir</button></form>
  </div>
</div>

<div class="tabs">
  <div class="tab active" data-tab="horario">Horario del Gym</div>
  <div class="tab" data-tab="maquinas">Maquinas</div>
  <div class="tab" data-tab="rutinas">Mis Rutinas</div>
</div>

<div class="content">

  <!-- TAB: HORARIO -->
  <div class="tab-content active" id="tab-horario">
    <div class="section-title">Horario del gimnasio</div>
    <div class="section-sub">Lunes a Viernes — actualizado en tiempo real</div>
    <div class="schedule-grid">
      <?php
      $hoy = date('N'); // 1=Lunes ... 7=Domingo
      foreach ($horario as $h):
          $diasMap = ['Lunes'=>1,'Martes'=>2,'Miercoles'=>3,'Jueves'=>4,'Viernes'=>5,'Sabado'=>6,'Domingo'=>7];
          $esHoy = $diasMap[$h['dia_semana']] == $hoy;
      ?>
      <div class="day-card <?= $esHoy ? 'today' : '' ?>">
        <div class="day-name"><?= $h['dia_semana'] ?> <?= $esHoy ? '• HOY' : '' ?></div>
        <?php if ($h['es_descanso']): ?>
          <div class="day-hours">Cerrado</div>
          <span class="day-status status-closed">Descanso</span>
        <?php else: ?>
          <div class="day-hours"><?= substr($h['hora_apertura'],0,5) ?> – <?= substr($h['hora_cierre'],0,5) ?></div>
          <span class="day-status status-open">Abierto</span>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- TAB: MAQUINAS -->
  <div class="tab-content" id="tab-maquinas">
    <div class="section-title">Funcionamiento de máquinas</div>
    <div class="section-sub">Consulta antes de asignar ejercicios a tus clientes</div>
    <div class="machines-grid">
      <?php foreach ($maquinas as $m): ?>
      <div class="machine-card">
        <div class="m-name"><?= htmlspecialchars($m['nombre']) ?></div>
        <div class="m-muscle"><?= htmlspecialchars($m['musculo_trabajado']) ?></div>
        <div class="m-desc"><?= htmlspecialchars($m['instrucciones']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- TAB: RUTINAS -->
  <div class="tab-content" id="tab-rutinas">
    <div class="section-title" style="display:flex;justify-content:space-between;align-items:center;">
      Mis rutinas creadas
      <button class="btn-primary" onclick="abrirModal()">+ Nueva rutina</button>
    </div>
    <div class="section-sub">Crea rutinas y asígnalas a tus clientes</div>

    <?php if (empty($rutinas)): ?>
      <p style="color:var(--muted);font-size:13px;">Aún no has creado rutinas.</p>
    <?php endif; ?>

    <?php foreach ($rutinas as $r):
        $stmtEj = $db->prepare("SELECT * FROM RUTINA_EJERCICIO WHERE id_rutina = ? ORDER BY orden");
        $stmtEj->execute([$r['id_rutina']]);
        $ejercicios = $stmtEj->fetchAll();
    ?>
    <div class="routine-card">
      <div class="routine-head">
        <span class="routine-name"><?= htmlspecialchars($r['nombre']) ?></span>
        <span class="routine-day"><?= $r['dia_semana'] ?></span>
      </div>
      <?php foreach ($ejercicios as $ej): ?>
      <div class="ejercicio-row">
        <span><b><?= htmlspecialchars($ej['nombre_ejercicio']) ?></b></span>
        <span><?= $ej['series'] ?> series × <?= $ej['repeticiones'] ?> reps</span>
      </div>
      <?php endforeach; ?>
      <div style="margin-top:12px;">
        <select class="select-cliente" data-rutina="<?= $r['id_rutina'] ?>" style="width:100%;background:var(--input-bg);border:1px solid var(--border);color:var(--text);padding:8px;border-radius:6px;font-size:12px;">
          <option value="">Asignar a cliente...</option>
          <?php foreach ($clientes as $c): ?>
            <option value="<?= $c['id_usuario'] ?>"><?= htmlspecialchars($c['nombre']) ?> (<?= htmlspecialchars($c['email']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

</div>

<!-- MODAL: Nueva rutina -->
<div class="modal-overlay" id="modalRutina">
  <div class="modal-box">
    <div class="modal-title">Crear nueva rutina</div>

    <div class="field">
      <label>Nombre de la rutina</label>
      <input type="text" id="rutinaNombre" placeholder="Ej. Pierna y glúteo">
    </div>

    <div class="field">
      <label>Día de la semana</label>
      <select id="rutinaDia">
        <option>Lunes</option><option>Martes</option><option>Miercoles</option>
        <option>Jueves</option><option>Viernes</option><option>Sabado</option><option>Domingo</option>
      </select>
    </div>

    <div class="field">
      <label>Ejercicios</label>
      <div id="ejerciciosContainer">
        <div class="ejercicio-input-row">
          <input type="text" class="ej-nombre" placeholder="Ej. Sentadilla">
          <input type="number" class="ej-series" placeholder="Series" value="3">
          <input type="number" class="ej-reps" placeholder="Reps" value="12">
        </div>
      </div>
      <button class="btn-add-ejercicio" onclick="agregarEjercicio()">+ Agregar ejercicio</button>
    </div>

    <div class="alert" id="modalAlert"></div>

    <div class="modal-actions">
      <button class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
      <button class="btn-primary" style="flex:1" onclick="guardarRutina()">Guardar rutina</button>
    </div>
  </div>
</div>

<script>
// Tabs
document.querySelectorAll('.tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    tab.classList.add('active');
    document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
  });
});

function abrirModal() { document.getElementById('modalRutina').classList.add('show'); }
function cerrarModal() { document.getElementById('modalRutina').classList.remove('show'); }

function agregarEjercicio() {
  const div = document.createElement('div');
  div.className = 'ejercicio-input-row';
  div.innerHTML = `
    <input type="text" class="ej-nombre" placeholder="Ej. Press banca">
    <input type="number" class="ej-series" placeholder="Series" value="3">
    <input type="number" class="ej-reps" placeholder="Reps" value="12">
  `;
  document.getElementById('ejerciciosContainer').appendChild(div);
}

async function guardarRutina() {
  const nombre = document.getElementById('rutinaNombre').value.trim();
  const dia    = document.getElementById('rutinaDia').value;

  const ejercicios = [];
  document.querySelectorAll('.ejercicio-input-row').forEach(row => {
    const nom = row.querySelector('.ej-nombre').value.trim();
    const ser = row.querySelector('.ej-series').value;
    const rep = row.querySelector('.ej-reps').value;
    if (nom) ejercicios.push({ nombre: nom, series: ser, repeticiones: rep });
  });

  if (!nombre || ejercicios.length === 0) {
    const al = document.getElementById('modalAlert');
    al.className = 'alert show error';
    al.textContent = 'Completa el nombre y al menos un ejercicio.';
    return;
  }

  try {
    const res = await fetch('/coach/crear_rutina_process.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ nombre, dia, ejercicios })
    });
    const data = await res.json();
    if (data.ok) {
      location.reload();
    } else {
      const al = document.getElementById('modalAlert');
      al.className = 'alert show error';
      al.textContent = data.msg || 'Error al guardar.';
    }
  } catch(e) {
    console.error(e);
  }
}

// Asignar rutina a cliente
document.querySelectorAll('.select-cliente').forEach(sel => {
  sel.addEventListener('change', async function() {
    const idRutina  = this.dataset.rutina;
    const idCliente = this.value;
    if (!idCliente) return;

    try {
      const res = await fetch('/coach/asignar_rutina_process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_rutina: idRutina, id_cliente: idCliente })
      });
      const data = await res.json();
      if (data.ok) {
        alert('Rutina asignada correctamente');
        this.value = '';
      } else {
        alert(data.msg || 'Error al asignar.');
      }
    } catch(e) {
      console.error(e);
    }
  });
});
</script>
</body>
</html>