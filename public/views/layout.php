<?php
if (!function_exists('render_app_layout')) {
    function render_app_layout(string $titulo, string $pagina, string $contenido, string $scriptsExtra = ''): void
    {
        $usuario = Auth::usuario();
        if ($usuario === null) {
            redirigir('/login');
        }

        $menu = Menu::items();
        if (!isset($menu[$pagina])) {
            http_response_code(404);
            exit('Página no encontrada.');
        }
        Auth::exigirRol(Menu::roles($pagina));

        $nombre = trim((string) ($usuario['nombre'] ?? 'Usuario'));
        $partesNombre = preg_split('/\s+/', $nombre, -1, PREG_SPLIT_NO_EMPTY);
        $iniciales = '';
        if (!empty($partesNombre)) {
            $iniciales = function_exists('mb_substr')
                ? mb_strtoupper(mb_substr($partesNombre[0], 0, 1))
                : strtoupper(substr($partesNombre[0], 0, 1));
            if (count($partesNombre) > 1) {
                $ultimaInicial = $partesNombre[count($partesNombre) - 1];
                $iniciales .= function_exists('mb_substr')
                    ? mb_strtoupper(mb_substr($ultimaInicial, 0, 1))
                    : strtoupper(substr($ultimaInicial, 0, 1));
            }
        }
        $rol = $usuario['rol'] ?? '';
        $nombreRol = Auth::ROLES[$rol] ?? ucfirst((string) $rol);
        $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $fechaHoy = date('j') . ' de ' . $meses[(int) date('n')] . ' de ' . date('Y');
        $iconos = [
            'dashboard' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
            'reservas' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 14h3"/>',
            'mesas' => '<rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="8" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/><rect x="13" y="13" width="8" height="8" rx="1"/>',
            'pedidos' => '<path d="M6 3h9l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M14 3v5h5M8 12h8M8 16h8"/>',
            'cocina' => '<path d="M8 21h8M12 17v4M7 3c0 2 2 2 2 4s-2 2-2 4M12 3c0 2 2 2 2 4s-2 2-2 4M17 3c0 2 2 2 2 4s-2 2-2 4M5 13h14l-1 4H6z"/>',
            'menu' => '<path d="M4 4h6a3 3 0 0 1 3 3v14a3 3 0 0 0-3-3H4zM20 4h-4a3 3 0 0 0-3 3v14a3 3 0 0 1 3-3h4z"/>',
            'cuentas' => '<rect x="3" y="5" width="18" height="15" rx="2"/><path d="M3 10h18M7 15h4"/>',
            'reportes' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
            'usuarios' => '<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM20 8v6M23 11h-6"/>',
            'config' => '<circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.7a8 8 0 0 1-1.5.9l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.5-.9l-1.7.7-1.4-2.4 1.4-1.1a7 7 0 0 1 0-1.8l-1.4-1.1 1.4-2.4 1.7.7a8 8 0 0 1 1.5-.9l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.5.9l1.7-.7 1.4 2.4-1.4 1.1a7 7 0 0 1 0 1.8z"/>',
            'logout' => '<path d="M10 17l5-5-5-5M15 12H3M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/>',
            'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
            'toggle' => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        ];
        ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo) ?> · Olivo Real</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link href="/css/estilos.css" rel="stylesheet">
</head>
<body>
<svg xmlns="http://www.w3.org/2000/svg" class="d-none" aria-hidden="true">
  <?php foreach ($iconos as $clave => $trazado): ?>
    <symbol id="icono-<?= e($clave) ?>" viewBox="0 0 24 24"><?= $trazado ?></symbol>
  <?php endforeach; ?>
</svg>
<aside class="sidebar" id="menuLateral">
  <div class="sidebar-logo">
    <div class="marca">Olivo Real</div>
    <div class="sub">RESTAURANTE</div>
  </div>
  <nav aria-label="Navegación principal">
    <?php foreach ($menu as $clave => [$texto, $icono, $url, $roles]): ?>
      <?php if (!Auth::tieneRol($roles)) continue; ?>
      <a class="enlace <?= $clave === $pagina ? 'activo' : '' ?>" href="<?= e($url) ?>" <?= $clave === $pagina ? 'aria-current="page"' : '' ?>>
        <svg class="nav-icon" aria-hidden="true"><use href="#icono-<?= e($clave) ?>"></use></svg><span><?= e($texto) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-pie">
    <a class="enlace" href="/logout"><svg class="nav-icon" aria-hidden="true"><use href="#icono-logout"></use></svg><span>Cerrar sesión</span></a>
  </div>
</aside>
<div class="overlay-menu" id="overlayMenu"></div>
<div class="contenido">
  <header class="topbar">
    <button class="icon-button d-lg-none" id="btnMenu" aria-label="Abrir menú"><svg aria-hidden="true"><use href="#icono-toggle"></use></svg></button>
    <div class="me-auto"><h1><?= e($titulo) ?></h1></div>
    <span class="fecha d-none d-md-block"><?= e($fechaHoy) ?></span>
    <button class="icon-button position-relative" type="button" aria-label="Notificaciones">
      <svg aria-hidden="true"><use href="#icono-bell"></use></svg>
    </button>
    <div class="d-flex align-items-center gap-2">
      <div class="avatar" aria-hidden="true"><?= e($iniciales) ?></div>
      <div class="d-none d-md-block lh-sm">
        <div class="fw-semibold small"><?= e($nombre) ?></div>
        <div class="text-secondary" style="font-size:.72rem"><?= e($nombreRol) ?></div>
      </div>
    </div>
  </header>
  <main class="pagina"><?= $contenido ?></main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/sistema.js"></script>
<?= $scriptsExtra ?>
</body>
</html>
<?php
    }
}
