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
<!-- Encabezado de la sección -->
<div class="mb-4">
  <h3 class="fw-bold text-dark mb-1">Historial de Asistencia</h3>
  <p class="text-muted small mb-0">Consulta y filtra los registros de entrada y salida del personal</p>
</div>

<!-- Formulario de filtros modernizado -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
  <form method="get" autocomplete="off">
    <div class="row g-3">
      <div class="col-12 col-md-3">
        <label class="form-label fw-semibold text-secondary small">Empleado</label>
        <input type="text" name="empleado" class="form-control form-control-sm bg-light border-0 py-2" value="<?= e($f['empleado']) ?>" placeholder="Nombre del empleado">
      </div>
      
      <div class="col-12 col-md-2">
        <label class="form-label fw-semibold text-secondary small">Cédula</label>
        <input type="text" name="cedula" class="form-control form-control-sm bg-light border-0 py-2" value="<?= e($f['cedula']) ?>" placeholder="Cédula">
      </div>
      
      <div class="col-6 col-md-2">
        <label class="form-label fw-semibold text-secondary small">Fecha específica</label>
        <input type="date" name="fecha" class="form-control form-control-sm bg-light border-0 py-2" value="<?= e($f['fecha']) ?>">
      </div>
      
      <div class="col-6 col-md-2">
        <label class="form-label fw-semibold text-secondary small">Desde</label>
        <input type="date" name="desde" class="form-control form-control-sm bg-light border-0 py-2" value="<?= e($f['desde']) ?>">
      </div>
      
      <div class="col-6 col-md-2">
        <label class="form-label fw-semibold text-secondary small">Hasta</label>
        <input type="date" name="hasta" class="form-control form-control-sm bg-light border-0 py-2" value="<?= e($f['hasta']) ?>">
      </div>
      
      <div class="col-6 col-md-1 d-flex align-items-end">
        <button class="btn btn-dark btn-sm w-100 fw-semibold py-2 shadow-sm">Buscar</button>
      </div>
    </div>
    
    <div class="mt-3 text-end">
      <a href="asistencias.php" class="text-decoration-none text-muted small fw-medium">Limpiar filtros</a>
    </div>
  </form>
</div>

<!-- Tabla de resultados dentro de una tarjeta -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light text-uppercase fs-7 text-secondary">
          <tr>
            <th class="py-3 ps-4">Cédula</th>
            <th class="py-3">Empleado</th>
            <th class="py-3">Fecha</th>
            <th class="py-3">Entrada</th>
            <th class="py-3">Salida</th>
            <th class="py-3 pe-4 text-end">Horas trabajadas</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($filas as $r): ?>
            <tr>
              <td class="ps-4 fw-medium text-dark"><?= e($r['cedula']) ?></td>
              <td>
                <div class="fw-semibold text-dark"><?= e($r['nombre']) ?></div>
              </td>
              <td class="text-muted"><?= fecha_cr($r['fecha']) ?></td>
              <td>
                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 fw-semibold"><?= substr($r['hora_entrada'], 0, 5) ?></span>
              </td>
              <td>
                <?= $r['hora_salida'] ? '<span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 fw-semibold">' . substr($r['hora_salida'], 0, 5) . '</span>' : '<span class="text-muted">—</span>' ?>
              </td>
              <td class="pe-4 text-end fw-semibold text-dark"><?= horas_trabajadas($r['hora_entrada'], $r['hora_salida']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$filas): ?>
            <tr>
              <td colspan="6" class="text-center py-5 text-muted">
                <div class="py-4">
                  <p class="mb-0">No se encontraron registros de asistencia con los filtros seleccionados.</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include '_footer.php'; ?>
