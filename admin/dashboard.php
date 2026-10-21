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
<h3 class="mb-4">Dashboard</h3>
<div class="row g-3">
<?php foreach ($tarjetas as $t): ?>
  <div class="col-12 col-sm-6 col-lg-4">
    <div class="card border-<?= $t[2] ?>">
      <div class="card-body">
        <div class="text-muted"><?= e($t[0]) ?></div>
        <div class="display-6"><?= (int)$t[1] ?></div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php include '_footer.php'; ?>
