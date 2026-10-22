<?php
require_once __DIR__ . '/../config.php';
requiere_login();

// Activar / desactivar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare('UPDATE empleados SET estado = 1 - estado WHERE id = ?')->execute([$id]);
    }
    header('Location: empleados.php');
    exit;
}

$empleados = $pdo->query('SELECT * FROM empleados ORDER BY nombre')->fetchAll();
include '_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Empleados</h3>
  <a href="empleado_form.php" class="btn btn-primary">Nuevo empleado</a>
</div>
<?php if (isset($_GET['ok'])): ?><div class="alert alert-success">Empleado guardado correctamente.</div><?php endif; ?>
<div class="table-responsive">
<table class="table table-striped table-bordered bg-white align-middle">
  <thead class="table-dark">
    <tr><th>Cédula</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Código</th><th>Estado</th><th>Registro</th><th>Acciones</th></tr>
  </thead>
  <tbody>
  <?php foreach ($empleados as $e): ?>
    <tr>
      <td><?= e($e['cedula']) ?></td>
      <td><?= e($e['nombre']) ?></td>
      <td><?= e($e['correo']) ?></td>
      <td><?= e($e['telefono']) ?></td>
      <td><code><?= e($e['codigo_acceso']) ?></code></td>
      <td><?= $e['estado'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?></td>
      <td><?= fecha_cr($e['fecha_registro']) ?></td>
      <td class="text-nowrap">
        <a class="btn btn-sm btn-outline-primary" href="empleado_form.php?id=<?= $e['id'] ?>">Editar</a>
        <form method="post" class="d-inline">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="id" value="<?= $e['id'] ?>">
          <button class="btn btn-sm <?= $e['estado'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
            <?= $e['estado'] ? 'Desactivar' : 'Activar' ?>
          </button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$empleados): ?><tr><td colspan="8" class="text-center">No hay empleados registrados.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
<?php include '_footer.php'; ?>
