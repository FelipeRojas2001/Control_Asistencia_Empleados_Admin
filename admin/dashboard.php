<?php
require_once __DIR__ . '/../config.php';
requiere_login();
$hoy = date('Y-m-d');

$total   = $pdo->query('SELECT COUNT(*) FROM empleados')->fetchColumn();
$activos = $pdo->query('SELECT COUNT(*) FROM empleados WHERE estado = 1')->fetchColumn();

$q = $pdo->prepare('SELECT COUNT(DISTINCT empleado_id) FROM asistencias WHERE fecha = ?');
$q->execute([$hoy]);
$ingresaron = $q->fetchColumn();

$q = $pdo->prepare('SELECT COUNT(DISTINCT empleado_id) FROM asistencias WHERE fecha = ? AND hora_salida IS NULL');
$q->execute([$hoy]);
$dentro = $q->fetchColumn();

$q = $pdo->prepare('SELECT (SELECT COUNT(*) FROM asistencias WHERE fecha = ?) + (SELECT COUNT(*) FROM asistencias WHERE fecha = ? AND hora_salida IS NOT NULL)');
$q->execute([$hoy, $hoy]);
$registros = $q->fetchColumn();

$tarjetas = [
    ['Total de empleados', $total, 'primary'],
    ['Empleados activos', $activos, 'success'],
    ['Ingresaron hoy', $ingresaron, 'info'],
    ['Actualmente dentro', $dentro, 'warning'],
    ['Registros del día (marcaciones)', $registros, 'secondary'],
];
include '_header.php';
?>
<!-- Encabezado de la sección -->
<div class="mb-4">
  <h3 class="fw-bold text-dark mb-1">Dashboard</h3>
  <p class="text-muted small mb-0">Resumen general de la actividad y estadísticas del sistema</p>
</div>

<!-- Cuadrícula de tarjetas de estadísticas -->
<div class="row g-4">
  <?php foreach ($tarjetas as $t): ?>
    <div class="col-12 col-sm-6 col-lg-4">
      <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-<?= $t[2] ?> py-2">
        <div class="card-body px-4">
          <div class="text-uppercase text-secondary fs-7 fw-semibold mb-1"><?= e($t[0]) ?></div>
          <div class="display-6 fw-bold text-dark mb-0"><?= (int)$t[1] ?></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php include '_footer.php'; ?>
