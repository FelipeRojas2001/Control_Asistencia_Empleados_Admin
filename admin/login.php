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
<title>Login administrativo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h4 class="text-center mb-3">Acceso administrativo</h4>
          <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
          <form method="post">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="mb-3"><label class="form-label">Usuario</label>
              <input type="text" name="usuario" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Contraseña</label>
              <input type="password" name="password" class="form-control" required></div>
            <button class="btn btn-dark w-100">Ingresar</button>
          </form>
          <div class="text-center mt-3"><a href="../index.php" class="small">Ir a marcación</a></div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
