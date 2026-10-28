<?php
require_once __DIR__ . '/../auth/guard.php';
requireRol('cliente', 'admin');
require_once __DIR__ . '/../config/db.php';

$db = getDB();
$idCliente = $_SESSION['id_usuario'];

// Día de hoy en español
$diasSemana = ['Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo'];
$hoyIndex   = date('N') - 1; // 0=Lunes ... 6=Domingo
$diaHoy     = $diasSemana[$hoyIndex];

// Horario completo del gym
$horario = $db->query("SELECT * FROM HORARIO_GYM ORDER BY 
    CASE dia_semana 
        WHEN 'Lunes' THEN 1 WHEN 'Martes' THEN 2 WHEN 'Miercoles' THEN 3
        WHEN 'Jueves' THEN 4 WHEN 'Viernes' THEN 5 WHEN 'Sabado' THEN 6 ELSE 7 END")->fetchAll();

// Info de hoy
$infoHoy = null;
foreach ($horario as $h) {
    if ($h['dia_semana'] === $diaHoy) { $infoHoy = $h; break; }
}

// Rutina asignada para HOY
$stmt = $db->prepare("
    SELECT r.id_rutina, r.nombre, r.dia_semana
    FROM ASIGNACION_RUTINA ar
    INNER JOIN RUTINA r ON r.id_rutina = ar.id_rutina
    WHERE ar.id_cliente = ? AND ar.activa = 1 AND r.dia_semana = ?
");
$stmt->execute([$idCliente, $diaHoy]);
$rutinaHoy = $stmt->fetch();

$ejerciciosHoy = [];
if ($rutinaHoy) {
    $stmtEj = $db->prepare("SELECT * FROM RUTINA_EJERCICIO WHERE id_rutina = ? ORDER BY orden");
    $stmtEj->execute([$rutinaHoy['id_rutina']]);
    $ejerciciosHoy = $stmtEj->fetchAll();
}

// Ver si ya completó el entrenamiento de hoy
$completadoHoy = false;
if ($rutinaHoy) {
    $chk = $db->prepare("SELECT completado FROM REGISTRO_ENTRENAMIENTO WHERE id_cliente=? AND id_rutina=? AND fecha=CAST(GETDATE() AS DATE)");
    $chk->execute([$idCliente, $rutinaHoy['id_rutina']]);
    $r = $chk->fetch();
    $completadoHoy = $r ? (bool)$r['completado'] : false;
}

// Todas las rutinas asignadas (para ver semana completa)
$stmtSemana = $db->prepare("
    SELECT r.id_rutina, r.nombre, r.dia_semana
    FROM ASIGNACION_RUTINA ar
    INNER JOIN RUTINA r ON r.id_rutina = ar.id_rutina
    WHERE ar.id_cliente = ? AND ar.activa = 1
    ORDER BY CASE r.dia_semana 
        WHEN 'Lunes' THEN 1 WHEN 'Martes' THEN 2 WHEN 'Miercoles' THEN 3
        WHEN 'Jueves' THEN 4 WHEN 'Viernes' THEN 5 WHEN 'Sabado' THEN 6 ELSE 7 END
");
$stmtSemana->execute([$idCliente]);
$rutinasSemana = $stmtSemana->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GymForce — Mi Rutina</title>
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
.badge-rol { background:rgba(6,214,160,.15); color:var(--green); padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; }
.btn-logout { background:var(--red); color:#fff; border:none; padding:8px 16px; border-radius:6px; cursor:pointer; font-size:12px; font-weight:600; }

.content { padding:28px; max-width:900px; margin:0 auto; }

/* Hero del día actual */
.today-hero {
  background:linear-gradient(135deg, #1a0a0a, #15151d);
  border:1px solid var(--border); border-radius:14px; padding:28px;
  margin-bottom:24px; position:relative; overflow:hidden;
}
.today-hero::before {
  content:''; position:absolute; top:-80px; right:-80px; width:250px; height:250px;
  background:radial-gradient(circle, var(--red-glow) 0%, transparent 70%);
}
.today-date { font-size:12px; color:var(--muted); text-transform:uppercase; letter-spacing:2px; }
.today-day { font-family:var(--ff-display); font-size:32px; color:#fff; margin:4px 0; text-transform:uppercase; }
.today-hours { font-size:13px; color:var(--orange); }
.today-status { display:inline-block; margin-top:10px; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:700; }
.status-open { background:rgba(6,214,160,.15); color:var(--green); }
.status-rest { background:rgba(230,48,48,.15); color:#ff8080; }

/* Card de rutina del día */
.routine-today { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:24px; margin-bottom:24px; }
.routine-today.rest-day { text-align:center; padding:40px; }
.routine-today .rest-icon { font-size:48px; margin-bottom:12px; }
.routine-today .rest-text { font-size:18px; font-weight:700; color:#fff; }
.routine-today .rest-sub { font-size:13px; color:var(--muted); margin-top:6px; }

.routine-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
.routine-title { font-size:18px; font-weight:700; color:#fff; }
.btn-complete { background:var(--green); color:#06251c; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-size:13px; font-weight:700; }
.btn-complete:disabled { background:var(--border); color:var(--muted); cursor:default; }

.ejercicio-item {
  display:flex; justify-content:space-between; align-items:center;
  padding:14px 0; border-top:1px solid var(--border);
}
.ejercicio-item:first-of-type { border-top:none; }
.ej-name { font-size:14px; font-weight:600; color:#fff; }
.ej-detail { font-size:12px; color:var(--muted); margin-top:2px; }
.ej-badge { background:var(--input-bg); color:var(--orange); padding:5px 12px; border-radius:6px; font-size:12px; font-weight:700; }

/* Semana completa */
.section-title { font-size:16px; font-weight:700; color:#fff; margin-bottom:14px; }
.week-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); gap:10px; }
.week-day { background:var(--card); border:1px solid var(--border); border-radius:8px; padding:14px; text-align:center; }
.week-day.is-today { border-color:var(--red); box-shadow:0 0 12px var(--red-glow); }
.week-day .wd-name { font-size:11px; font-weight:700; color:var(--muted); text-transform:uppercase; }
.week-day .wd-routine { font-size:12px; color:#fff; margin-top:6px; }
.week-day .wd-empty { font-size:11px; color:var(--muted); margin-top:6px; font-style:italic; }

.alert { margin-top:14px; padding:10px 14px; border-radius:6px; font-size:12px; display:none; }
.alert.show { display:block; }
.alert.success { background:rgba(40,200,100,.1); color:#5de89e; }
</style>
</head>
<body>

<div class="topbar">
  <div class="logo">GYM<span>FORCE</span></div>
  <div class="user-info">
    <span class="badge-rol">Cliente</span>
    Hola, <strong style="color:#fff"><?= htmlspecialchars($_SESSION['nombre']) ?></strong>
    <form method="POST" action="/auth/logout.php"><button class="btn-logout">Salir</button></form>
  </div>
</div>

<div class="content">

  <!-- HERO DEL DÍA -->
  <div class="today-hero">
   <div class="today-date"><?= date('d \d\e F, Y') ?></div>
    <div class="today-day"><?= $diaHoy ?></div>
    <?php if ($infoHoy && !$infoHoy['es_descanso']): ?>
      <div class="today-hours">Abierto de <?= substr($infoHoy['hora_apertura'],0,5) ?> a <?= substr($infoHoy['hora_cierre'],0,5) ?></div>
      <span class="today-status status-open">Gimnasio abierto</span>
    <?php else: ?>
      <div class="today-hours">El gimnasio no abre hoy</div>
      <span class="today-status status-rest">Día de descanso</span>
    <?php endif; ?>
  </div>

  <!-- RUTINA DE HOY -->
  <?php if (!$infoHoy || $infoHoy['es_descanso']): ?>
    <div class="routine-today rest-day">
      <div class="rest-icon">-</div>
      <div class="rest-text">Hoy es día de descanso</div>
      <div class="rest-sub">Aprovecha para recuperarte. Regresa mañana con todo</div>
    </div>
  <?php elseif (!$rutinaHoy): ?>
    <div class="routine-today rest-day">
      <div class="rest-icon">-</div>
      <div class="rest-text">Aún no tienes rutina asignada para hoy</div>
      <div class="rest-sub">Pídele a tu coach que te asigne una rutina para <?= $diaHoy ?></div>
    </div>
  <?php else: ?>
    <!-- Componente React: se monta aquí con los datos que entrega PHP -->
    <div id="rutina-hoy-root"></div>
  <?php endif; ?>

  <!-- SEMANA COMPLETA -->
  <div class="section-title">Tu semana de entrenamiento</div>
  <div class="week-grid">
    <?php foreach (['Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo'] as $d):
        $rutinaDia = null;
        foreach ($rutinasSemana as $rs) { if ($rs['dia_semana'] === $d) { $rutinaDia = $rs; break; } }
        $esHoyDia = $d === $diaHoy;
    ?>
    <div class="week-day <?= $esHoyDia ? 'is-today' : '' ?>">
      <div class="wd-name"><?= $d ?></div>
      <?php if ($rutinaDia): ?>
        <div class="wd-routine"><?= htmlspecialchars($rutinaDia['nombre']) ?></div>
      <?php else: ?>
        <div class="wd-empty">Sin asignar</div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

</div>

<?php if ($rutinaHoy && $infoHoy && !$infoHoy['es_descanso']): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/react/18.3.1/umd/react.production.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/18.3.1/umd/react-dom.production.min.js"></script>
<script>
// Datos iniciales entregados por PHP (JSON_HEX_* evita romper el <script>)
const RUTINA_HOY = <?= json_encode([
    'id'          => (int)$rutinaHoy['id_rutina'],
    'nombre'      => $rutinaHoy['nombre'],
    'completado'  => $completadoHoy,
    'ejercicios'  => array_map(fn($e) => [
        'nombre'      => $e['nombre_ejercicio'],
        'descanso'    => $e['descanso_segundos'],
        'series'      => $e['series'],
        'repeticiones'=> $e['repeticiones'],
    ], $ejerciciosHoy),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;

const h = React.createElement;
const { useState } = React;

function Ejercicio({ ej }) {
  return h('div', { className: 'ejercicio-item' },
    h('div', null,
      h('div', { className: 'ej-name' }, ej.nombre),
      h('div', { className: 'ej-detail' }, 'Descanso: ' + ej.descanso + 's entre series')
    ),
    h('span', { className: 'ej-badge' }, ej.series + ' x ' + ej.repeticiones)
  );
}

function RutinaHoy({ rutina }) {
  const [completado, setCompletado] = useState(rutina.completado);
  const [enviando, setEnviando] = useState(false);
  const [mensaje, setMensaje] = useState('');

  async function marcarCompletado() {
    setEnviando(true);
    try {
      const res = await fetch('/cliente/marcar_completado_process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_rutina: rutina.id })
      });
      const data = await res.json();
      if (data.ok) {
        setCompletado(true);
        setMensaje('Buen trabajo! Entrenamiento registrado');
      }
    } catch (e) {
      console.error(e);
    } finally {
      setEnviando(false);
    }
  }

  return h('div', { className: 'routine-today' },
    h('div', { className: 'routine-header' },
      h('span', { className: 'routine-title' }, rutina.nombre),
      h('button', {
        className: 'btn-complete',
        disabled: completado || enviando,
        onClick: marcarCompletado
      }, completado ? 'Completado' : 'Marcar como hecho')
    ),
    rutina.ejercicios.map((ej, i) => h(Ejercicio, { key: i, ej })),
    h('div', { className: 'alert success' + (mensaje ? ' show' : '') }, mensaje)
  );
}

ReactDOM.createRoot(document.getElementById('rutina-hoy-root'))
  .render(h(RutinaHoy, { rutina: RUTINA_HOY }));
</script>
<?php endif; ?>
</body>
</html>