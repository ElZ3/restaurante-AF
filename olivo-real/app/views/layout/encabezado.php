<?php
/**
 * Plantilla compartida: <head>, menú lateral y barra superior.
 * La carga render() antes de cada vista. El controlador le pasa:
 *   'titulo' => 'Dashboard',
 *   'pagina' => 'dashboard',   // clave del menú activo
 */
$titulo ??= 'Olivo Real';
$pagina ??= '';

$usuario = Auth::usuario();   // ['id', 'nombre', 'rol'] de la sesión

$menu = Menu::items();   // clave => [texto, icono, archivo, roles]

$meses = ['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$fecha_hoy = date('j') . ' de ' . $meses[(int)date('n')] . ' de ' . date('Y');
$iniciales = mb_strtoupper(mb_substr($usuario['nombre'], 0, 1) . mb_substr(strrchr(' ' . $usuario['nombre'], ' '), 1, 1));
$nombres_rol = Auth::ROLES;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($titulo) ?> · Olivo Real</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link href="css/estilos.css" rel="stylesheet">
</head>
<body>

<aside class="sidebar" id="menuLateral">
  <div class="sidebar-logo">
    <div class="marca">Olivo Real</div>
    <div class="sub">RESTAURANTE</div>
  </div>
  <nav>
    <?php foreach ($menu as $clave => [$texto, $icono, $url, $roles]): ?>
      <?php if (!in_array($usuario['rol'], $roles, true)) continue; ?>
      <a class="enlace <?= $clave === $pagina ? 'activo' : '' ?>" href="<?= $url ?>">
        <i class="bi <?= $icono ?>"></i><span><?= $texto ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-pie">
    <a class="enlace" href="cerrar_sesion.php"><i class="bi bi-box-arrow-left"></i><span>Cerrar sesión</span></a>
  </div>
</aside>
<div class="overlay-menu" id="overlayMenu"></div>

<div class="contenido">
  <header class="topbar">
    <button class="btn btn-outline-olivo d-lg-none" id="btnMenu" aria-label="Abrir menú"><i class="bi bi-list"></i></button>
    <div class="me-auto">
      <h1><?= htmlspecialchars($titulo) ?></h1>
    </div>
    <span class="fecha d-none d-md-block"><?= $fecha_hoy ?></span>
    <button class="btn btn-link text-secondary position-relative p-1" aria-label="Notificaciones">
      <i class="bi bi-bell fs-5"></i>
    </button>
    <div class="d-flex align-items-center gap-2">
      <div class="avatar"><?= $iniciales ?></div>
      <div class="d-none d-md-block lh-sm">
        <div class="fw-semibold small"><?= htmlspecialchars($usuario['nombre']) ?></div>
        <div class="text-secondary" style="font-size:.72rem"><?= $nombres_rol[$usuario['rol']] ?></div>
      </div>
    </div>
  </header>
  <main class="pagina">