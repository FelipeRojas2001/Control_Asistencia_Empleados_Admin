<?php
require_once __DIR__ . '/../config.php';
requiere_login();
$pagina = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Administración - Control de asistencia</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Asistencia</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= $pagina=='dashboard.php'?'active':'' ?>" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= $pagina=='empleados.php'?'active':'' ?>" href="empleados.php">Empleados</a></li>
        <li class="nav-item"><a class="nav-link <?= $pagina=='empleado_form.php'?'active':'' ?>" href="empleado_form.php">Nuevo empleado</a></li>
        <li class="nav-item"><a class="nav-link <?= $pagina=='asistencias.php'?'active':'' ?>" href="asistencias.php">Historial</a></li>
        <li class="nav-item"><a class="nav-link" href="../index.php" target="_blank">Pantalla de marcación</a></li>
      </ul>
      <span class="navbar-text me-3"><?= e($_SESSION['admin_nombre']) ?></span>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Salir</a>
    </div>
  </div>
</nav>
<div class="container">
