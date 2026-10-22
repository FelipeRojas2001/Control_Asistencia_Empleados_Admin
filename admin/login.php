<?php
require_once __DIR__ . '/../config.php';

if (!empty($_SESSION['admin_id'])) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($usuario === '' || $password === '') {
        $error = 'Todos los campos son obligatorios.';
    } else {
        $st = $pdo->prepare('SELECT * FROM usuarios WHERE usuario = ? AND estado = 1');
        $st->execute([$usuario]);
        $u = $st->fetch();
        if ($u && password_verify($password, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $u['id'];
            $_SESSION['admin_nombre'] = $u['nombre'];
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login Administrativo</title>
<!-- Bootstrap 5.3.3 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark bg-gradient d-flex align-items-center min-vh-100">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5 col-xl-4">
      <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
        <div class="card-body p-0">
          
          <!-- Encabezado con distintivo de administración -->
          <div class="text-center mb-4">
            <div class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill mb-3 fw-semibold small">
              Área Restringida
            </div>
            <h4 class="fw-bold text-dark mb-1">Acceso Administrativo</h4>
            <p class="text-muted small">Panel de control y gestión del sistema</p>
          </div>

          <!-- Mensaje de error PHP original -->
          <?php if ($error): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4 small py-3">
              <strong><?= e($error) ?></strong>
            </div>
          <?php endif; ?>

          <!-- Formulario original -->
          <form method="post" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            
            <div class="mb-3">
              <label class="form-label fw-semibold text-secondary small">Usuario</label>
              <input type="text" name="usuario" class="form-control form-control-lg fs-6 bg-light border-0" placeholder="Usuario administrador" required>
            </div>
            
            <div class="mb-4">
              <label class="form-label fw-semibold text-secondary small">Contraseña</label>
              <input type="password" name="password" class="form-control form-control-lg fs-6 bg-light border-0" placeholder="••••••••" required>
            </div>
            
            <button class="btn btn-primary btn-lg w-100 fs-6 fw-semibold shadow-sm py-2">Ingresar al Sistema</button>
          </form>

          <!-- Enlace de retorno -->
          <div class="text-center mt-4">
            <a href="../index.php" class="text-decoration-none text-muted small fw-medium">← Volver a marcación</a>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
