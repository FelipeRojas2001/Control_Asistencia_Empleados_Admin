<?php
require_once __DIR__ . '/config.php';

$msg = '';
$tipo = '';
$detalle = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $cedula = trim($_POST['cedula'] ?? '');
    $codigo = strtoupper(trim($_POST['codigo'] ?? ''));

    if ($cedula === '' || $codigo === '') {
        $msg = 'Todos los campos son obligatorios.';
        $tipo = 'danger';
    } else {
        $st = $pdo->prepare('SELECT * FROM empleados WHERE cedula = ?');
        $st->execute([$cedula]);
        $emp = $st->fetch();

        if (!$emp || !hash_equals($emp['codigo_acceso'], $codigo)) {
            $msg = 'Cédula o código de acceso incorrecto.';
            $tipo = 'danger';
        } elseif ((int)$emp['estado'] !== 1) {
            $msg = 'El empleado se encuentra inactivo.';
            $tipo = 'warning';
        } else {
            $hoy = date('Y-m-d');
            $ahora = date('H:i:s');
            $pdo->beginTransaction();
            $q = $pdo->prepare('SELECT id FROM asistencias WHERE empleado_id = ? AND fecha = ? AND hora_salida IS NULL ORDER BY id DESC LIMIT 1 FOR UPDATE');
            $q->execute([$emp['id'], $hoy]);
            $abierta = $q->fetch();

            if ($abierta) {
                $pdo->prepare('UPDATE asistencias SET hora_salida = ? WHERE id = ?')->execute([$ahora, $abierta['id']]);
                $msg = 'Salida registrada correctamente.';
                $etq = 'Hora de salida';
            } else {
                $pdo->prepare('INSERT INTO asistencias (empleado_id, fecha, hora_entrada) VALUES (?, ?, ?)')->execute([$emp['id'], $hoy, $ahora]);
                $msg = 'Entrada registrada correctamente.';
                $etq = 'Hora de entrada';
            }
            $pdo->commit();
            $tipo = 'success';
            $detalle = 'Empleado: ' . e($emp['nombre']) . '<br>Fecha: ' . fecha_cr($hoy) . '<br>' . $etq . ': ' . date('h:i A', strtotime($ahora));
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Marcación de asistencia</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h4 class="text-center mb-3">Registrar asistencia</h4>
          <?php if ($msg): ?>
            <div class="alert alert-<?= $tipo ?>">
              <strong><?= e($msg) ?></strong>
              <?php if ($detalle): ?><div class="mt-2"><?= $detalle ?></div><?php endif; ?>
            </div>
          <?php endif; ?>
          <form method="post" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="mb-3">
              <label class="form-label">Cédula</label>
              <input type="text" name="cedula" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Código de acceso</label>
              <input type="text" name="codigo" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">Registrar</button>
          </form>
          <div class="text-center mt-3"><a href="admin/login.php" class="small">Acceso administrativo</a></div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
