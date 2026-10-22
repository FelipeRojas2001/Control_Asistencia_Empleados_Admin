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
<!-- Encabezado de la sección -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
  <div>
    <h3 class="fw-bold text-dark mb-1">Empleados</h3>
    <p class="text-muted small mb-0">Gestión y control del personal registrado en el sistema</p>
  </div>
  <a href="empleado_form.php" class="btn btn-dark fw-semibold shadow-sm px-3 py-2">
    <span class="me-1">+</span> Nuevo empleado
  </a>
</div>

<!-- Alerta de éxito -->
<?php if (isset($_GET['ok'])): ?>
  <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
    <strong>¡Éxito!</strong> Empleado guardado correctamente.
  </div>
<?php endif; ?>

<!-- Tarjeta contenedora de la tabla -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light text-uppercase fs-7 text-secondary">
          <tr>
            <th class="py-3 ps-4">Cédula</th>
            <th class="py-3">Nombre</th>
            <th class="py-3">Correo</th>
            <th class="py-3">Teléfono</th>
            <th class="py-3">Código</th>
            <th class="py-3">Estado</th>
            <th class="py-3">Registro</th>
            <th class="py-3 text-end pe-4">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($empleados as $e): ?>
            <tr>
              <td class="ps-4 fw-medium text-dark"><?= e($e['cedula']) ?></td>
              <td>
                <div class="fw-semibold text-dark"><?= e($e['nombre']) ?></div>
              </td>
              <td class="text-muted"><?= e($e['correo']) ?></td>
              <td class="text-muted"><?= e($e['telefono']) ?></td>
              <td><code class="bg-light px-2 py-1 rounded text-dark border"><?= e($e['codigo_acceso']) ?></code></td>
              <td>
                <?= $e['estado'] ? '<span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold">Activo</span>' : '<span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill fw-semibold">Inactivo</span>' ?>
              </td>
              <td class="text-muted small"><?= fecha_cr($e['fecha_registro']) ?></td>
              <td class="text-end pe-4 text-nowrap">
                <a class="btn btn-sm btn-light border fw-semibold px-2 py-1 me-1 text-secondary" href="empleado_form.php?id=<?= $e['id'] ?>">Editar</a>
                <form method="post" class="d-inline">
                  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                  <input type="hidden" name="id" value="<?= $e['id'] ?>">
                  <button class="btn btn-sm <?= $e['estado'] ? 'btn-outline-danger' : 'btn-outline-success' ?> fw-semibold px-2 py-1">
                    <?= $e['estado'] ? 'Desactivar' : 'Activar' ?>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$empleados): ?>
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">
                <div class="py-4">
                  <p class="mb-0">No hay empleados registrados actualmente.</p>
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
