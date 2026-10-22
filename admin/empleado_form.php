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
<h3 class="mb-3"><?= $id ? 'Editar empleado' : 'Registrar empleado' ?></h3>
<?php foreach ($errores as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" class="card card-body" style="max-width:600px">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <input type="hidden" name="id" value="<?= $id ?>">
  <div class="mb-3"><label class="form-label">Cédula</label>
    <input type="text" name="cedula" class="form-control" value="<?= e($emp['cedula']) ?>" required></div>
  <div class="mb-3"><label class="form-label">Nombre completo</label>
    <input type="text" name="nombre" class="form-control" value="<?= e($emp['nombre']) ?>" required></div>
  <div class="mb-3"><label class="form-label">Correo</label>
    <input type="email" name="correo" class="form-control" value="<?= e($emp['correo']) ?>" required></div>
  <div class="mb-3"><label class="form-label">Teléfono</label>
    <input type="text" name="telefono" class="form-control" value="<?= e($emp['telefono']) ?>" required></div>
  <?php if ($id): ?>
    <div class="mb-3"><label class="form-label">Código de acceso</label>
      <input type="text" class="form-control" value="<?= e($emp['codigo_acceso']) ?>" readonly></div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="estado" id="estado" <?= $emp['estado'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="estado">Activo</label>
    </div>
  <?php else: ?>
    <p class="text-muted">El código de acceso se genera automáticamente al guardar.</p>
  <?php endif; ?>
  <div>
    <button class="btn btn-primary">Guardar</button>
    <a href="empleados.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>
<?php include '_footer.php'; ?>
