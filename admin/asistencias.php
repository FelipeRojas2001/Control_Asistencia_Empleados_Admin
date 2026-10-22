<?php
require_once __DIR__ . '/../config.php';
requiere_login();

$f = [
    'empleado' => trim($_GET['empleado'] ?? ''),
    'cedula'   => trim($_GET['cedula'] ?? ''),
    'fecha'    => trim($_GET['fecha'] ?? ''),
    'desde'    => trim($_GET['desde'] ?? ''),
    'hasta'    => trim($_GET['hasta'] ?? ''),
];
$esFecha = fn($v) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $v);

$where = [];
$params = [];
if ($f['empleado'] !== '') { $where[] = 'e.nombre LIKE ?'; $params[] = '%' . $f['empleado'] . '%'; }
if ($f['cedula'] !== '')   { $where[] = 'e.cedula LIKE ?'; $params[] = '%' . $f['cedula'] . '%'; }
if ($esFecha($f['fecha'])) { $where[] = 'a.fecha = ?';  $params[] = $f['fecha']; }
if ($esFecha($f['desde'])) { $where[] = 'a.fecha >= ?'; $params[] = $f['desde']; }
if ($esFecha($f['hasta'])) { $where[] = 'a.fecha <= ?'; $params[] = $f['hasta']; }

$sql = 'SELECT a.*, e.cedula, e.nombre FROM asistencias a JOIN empleados e ON e.id = a.empleado_id'
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . ' ORDER BY a.fecha DESC, a.hora_entrada DESC LIMIT 500';
$st = $pdo->prepare($sql);
$st->execute($params);
$filas = $st->fetchAll();
include '_header.php';
?>
<h3 class="mb-3">Historial de asistencia</h3>
<form method="get" class="card card-body mb-3">
  <div class="row g-2">
    <div class="col-12 col-md-3"><label class="form-label">Empleado</label>
      <input type="text" name="empleado" class="form-control" value="<?= e($f['empleado']) ?>"></div>
    <div class="col-12 col-md-2"><label class="form-label">Cédula</label>
      <input type="text" name="cedula" class="form-control" value="<?= e($f['cedula']) ?>"></div>
    <div class="col-6 col-md-2"><label class="form-label">Fecha</label>
      <input type="date" name="fecha" class="form-control" value="<?= e($f['fecha']) ?>"></div>
    <div class="col-6 col-md-2"><label class="form-label">Desde</label>
      <input type="date" name="desde" class="form-control" value="<?= e($f['desde']) ?>"></div>
    <div class="col-6 col-md-2"><label class="form-label">Hasta</label>
      <input type="date" name="hasta" class="form-control" value="<?= e($f['hasta']) ?>"></div>
    <div class="col-6 col-md-1 d-flex align-items-end gap-1">
      <button class="btn btn-primary">Buscar</button>
    </div>
  </div>
  <div class="mt-2"><a href="asistencias.php" class="small">Limpiar filtros</a></div>
</form>
<div class="table-responsive">
<table class="table table-striped table-bordered bg-white">
  <thead class="table-dark">
    <tr><th>Cédula</th><th>Empleado</th><th>Fecha</th><th>Entrada</th><th>Salida</th><th>Horas trabajadas</th></tr>
  </thead>
  <tbody>
  <?php foreach ($filas as $r): ?>
    <tr>
      <td><?= e($r['cedula']) ?></td>
      <td><?= e($r['nombre']) ?></td>
      <td><?= fecha_cr($r['fecha']) ?></td>
      <td><?= substr($r['hora_entrada'], 0, 5) ?></td>
      <td><?= $r['hora_salida'] ? substr($r['hora_salida'], 0, 5) : '—' ?></td>
      <td><?= horas_trabajadas($r['hora_entrada'], $r['hora_salida']) ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$filas): ?><tr><td colspan="6" class="text-center">Sin registros.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
<?php include '_footer.php'; ?>
