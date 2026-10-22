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
<title>Marcación de Asistencia</title>
<!-- Bootstrap 5.3.3 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5 col-xl-4">
      <div class="card border-0 shadow-lg rounded-4 p-4 p-md-4">
        <div class="card-body p-0">
          
          <!-- Encabezado -->
          <div class="text-center mb-4">
            <h4 class="fw-bold text-dark mb-1">Registrar asistencia</h4>
            <p class="text-muted small">Ingresa tus datos para continuar</p>
          </div>

          <!-- Mensaje PHP original -->
          <?php if ($msg): ?>
            <div class="alert alert-<?= $tipo ?> border-0 shadow-sm mb-4">
              <strong><?= e($msg) ?></strong>
              <?php if ($detalle): ?><div class="mt-2 small text-muted"><?= $detalle ?></div><?php endif; ?>
            </div>
          <?php endif; ?>

          <!-- Formulario original -->
          <form method="post" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            
            <div class="mb-3">
              <label class="form-label fw-semibold text-secondary small">Cédula</label>
              <input type="text" name="cedula" class="form-control form-control-lg fs-6 bg-light border-0" placeholder="Ej. 12345678" required>
            </div>
            
            <div class="mb-4">
              <label class="form-label fw-semibold text-secondary small">Código de acceso</label>
              <input type="password" name="codigo" class="form-control form-control-lg fs-6 bg-light border-0" placeholder="••••••••" required>
            </div>
            
            <button class="btn btn-dark btn-lg w-100 fs-6 fw-semibold shadow-sm py-2">Registrar</button>
          </form>

          <!-- Enlace administrativo -->
          <div class="text-center mt-4">
            <a href="admin/login.php" class="text-decoration-none text-muted small fw-medium">Acceso administrativo</a>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
