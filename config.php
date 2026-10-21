<?php
// Configuración general.
session_start();
date_default_timezone_set('America/Costa_Rica');

$pdo = new PDO(
    'mysql:host=localhost;dbname=control_asistencia;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function requiere_login() {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_check() {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        http_response_code(400);
        exit('Solicitud inválida (token CSRF).');
    }
}

function generar_codigo(PDO $pdo) {
    do {
        $c = strtoupper(bin2hex(random_bytes(3))); // ej: A7F294
        $s = $pdo->prepare('SELECT 1 FROM empleados WHERE codigo_acceso = ?');
        $s->execute([$c]);
    } while ($s->fetch());
    return $c;
}

function horas_trabajadas($entrada, $salida) {
    if (!$salida) return '—';
    $d = max(0, strtotime($salida) - strtotime($entrada));
    return sprintf('%d:%02d', intdiv($d, 3600), intdiv($d % 3600, 60));
}

function fecha_cr($f) { return date('d/m/Y', strtotime($f)); }
