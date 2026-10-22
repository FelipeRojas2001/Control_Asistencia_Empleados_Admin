<?php
require_once __DIR__ . '/../config.php';
requiere_login();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$emp = ['cedula' => '', 'nombre' => '', 'correo' => '', 'telefono' => '', 'estado' => 1, 'codigo_acceso' => ''];

if ($id > 0) {
    $st = $pdo->prepare('SELECT * FROM empleados WHERE id = ?');
    $st->execute([$id]);
    $emp = $st->fetch();
    if (!$emp) { header('Location: empleados.php'); exit; }
}

$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $emp['cedula']   = trim($_POST['cedula'] ?? '');
    $emp['nombre']   = trim($_POST['nombre'] ?? '');
    $emp['correo']   = trim($_POST['correo'] ?? '');
    $emp['telefono'] = trim($_POST['telefono'] ?? '');
    $emp['estado']   = isset($_POST['estado']) ? 1 : 0;

    if ($emp['cedula'] === '' || $emp['nombre'] === '' || $emp['correo'] === '' || $emp['telefono'] === '') {
        $errores[] = 'Todos los campos son obligatorios.';
    }
    if ($emp['cedula'] !== '' && !preg_match('/^[0-9A-Za-z-]{5,20}$/', $emp['cedula'])) {
        $errores[] = 'La cédula solo puede contener letras, números y guiones (5 a 20 caracteres).';
    }
    if ($emp['correo'] !== '' && !filter_var($emp['correo'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido.';
    }
    if ($emp['telefono'] !== '' && !preg_match('/^[0-9+\- ]{8,20}$/', $emp['telefono'])) {
        $errores[] = 'El teléfono no es válido (8 a 20 dígitos).';
    }
    if (mb_strlen($emp['nombre']) > 100) $errores[] = 'El nombre es demasiado largo.';

    if (!$errores) {
        $st = $pdo->prepare('SELECT id FROM empleados WHERE cedula = ? AND id <> ?');
        $st->execute([$emp['cedula'], $id]);
        if ($st->fetch()) $errores[] = 'Ya existe un empleado con esa cédula.';
    }

    if (!$errores) {
        if ($id > 0) {
            $pdo->prepare('UPDATE empleados SET cedula=?, nombre=?, correo=?, telefono=?, estado=? WHERE id=?')
                ->execute([$emp['cedula'], $emp['nombre'], $emp['correo'], $emp['telefono'], $emp['estado'], $id]);
        } else {
            $pdo->prepare('INSERT INTO empleados (cedula, nombre, correo, telefono, codigo_acceso, estado) VALUES (?,?,?,?,?,1)')
                ->execute([$emp['cedula'], $emp['nombre'], $emp['correo'], $emp['telefono'], generar_codigo($pdo)]);
        }
        header('Location: empleados.php?ok=1');
        exit;
    }
}
include '_header.php';
?>
<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-lg-6">
    
    <!-- Encabezado de la sección -->
    <div class="mb-4">
      <h3 class="fw-bold text-dark mb-1"><?= $id ? 'Editar empleado' : 'Registrar empleado' ?></h3>
      <p class="text-muted small mb-0">Completa los campos necesarios para la gestión del personal</p>
    </div>

    <!-- Alertas de errores PHP originales -->
    <?php foreach ($errores as $er): ?>
      <div class="alert alert-danger border-0 shadow-sm rounded-4 py-2 px-3 mb-3 small">
        <strong><?= e($er) ?></strong>
      </div>
    <?php endforeach; ?>

    <!-- Tarjeta del formulario -->
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-4">
      <div class="card-body p-0">
        
        <form method="post" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="id" value="<?= $id ?>">
          
          <div class="mb-3">
            <label class="form-label fw-semibold text-secondary small">Cédula</label>
            <input type="text" name="cedula" class="form-control form-control-lg fs-6 bg-light border-0" value="<?= e($emp['cedula']) ?>" placeholder="Ej. 12345678" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-semibold text-secondary small">Nombre completo</label>
            <input type="text" name="nombre" class="form-control form-control-lg fs-6 bg-light border-0" value="<?= e($emp['nombre']) ?>" placeholder="Ej. Juan Pérez" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-semibold text-secondary small">Correo</label>
            <input type="email" name="correo" class="form-control form-control-lg fs-6 bg-light border-0" value="<?= e($emp['correo']) ?>" placeholder="correo@ejemplo.com" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-semibold text-secondary small">Teléfono</label>
            <input type="text" name="telefono" class="form-control form-control-lg fs-6 bg-light border-0" value="<?= e($emp['telefono']) ?>" placeholder="Ej. 88888888" required>
          </div>
          
          <?php if ($id): ?>
            <div class="mb-3">
              <label class="form-label fw-semibold text-secondary small">Código de acceso</label>
              <input type="text" class="form-control form-control-lg fs-6 bg-light border-0 text-muted" value="<?= e($emp['codigo_acceso']) ?>" readonly>
            </div>
            
            <div class="form-check form-switch mb-4">
              <input class="form-check-input" type="checkbox" role="switch" name="estado" id="estado" <?= $emp['estado'] ? 'checked' : '' ?>>
              <label class="form-check-label fw-semibold small text-secondary ms-2" for="estado">Empleado Activo</label>
            </div>
          <?php else: ?>
            <div class="alert alert-light border-0 bg-light rounded-3 text-muted small mb-4">
              💡 El código de acceso se generará automáticamente al guardar.
            </div>
          <?php endif; ?>
          
          <div class="d-flex gap-2 pt-2">
            <button class="btn btn-dark fw-semibold px-4 py-2 shadow-sm">Guardar</button>
            <a href="empleados.php" class="btn btn-light border fw-semibold px-4 py-2 text-secondary">Cancelar</a>
          </div>
        </form>

      </div>
    </div>

  </div>
</div>
<?php include '_footer.php'; ?>
