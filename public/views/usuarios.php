<?php
// Vista: usuarios.php
// Gestión de usuarios - Olivo Real Restaurant.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios · Olivo Real Restaurant</title>
    <link rel="stylesheet" href="/css/estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', Arial, sans-serif; background: var(--marfil); color: var(--texto); }
        :root { --verde: #183D34; --verde-2: #225247; --dorado: #D8BD82; --dorado-osc: #B99A56; --marfil: #F7F3EA; --blanco: #FFFFFF; --texto: #2E2E2E; --gris: #6B7280; --borde: #E5E1D6; --radio: 12px; --sombra: 0 1px 3px rgba(24,61,52,.08), 0 4px 14px rgba(24,61,52,.05); --ancho-menu: 250px; }
        .sidebar { position: fixed; inset: 0 auto 0 0; width: var(--ancho-menu); background: var(--verde); color: #fff; display: flex; flex-direction: column; z-index: 1040; transition: transform .25s ease; }
        .sidebar-logo { padding: 1.4rem 1.25rem 1rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,.1); }
        .sidebar-logo .marca { font-family: 'Playfair Display', Georgia, serif; font-size: 1.6rem; font-weight: 700; color: #fff; line-height: 1; }
        .sidebar-logo .sub { font-size: .65rem; letter-spacing: .35em; color: var(--dorado); margin-top: .3rem; text-transform: uppercase; }
        .sidebar nav { padding: .75rem; overflow-y: auto; flex: 1; }
        .sidebar .enlace { display: flex; align-items: center; gap: .75rem; padding: .65rem .85rem; border-radius: 8px; color: rgba(255,255,255,.78); text-decoration: none; font-size: .9rem; margin-bottom: 2px; }
        .sidebar .enlace:hover { background: rgba(255,255,255,.08); color: #fff; }
        .sidebar .enlace.activo { background: var(--dorado); color: var(--verde); font-weight: 600; }
        .sidebar-pie { padding: .75rem; border-top: 1px solid rgba(255,255,255,.1); }
        .contenido { margin-left: var(--ancho-menu); min-height: 100vh; }
        .topbar { background: var(--blanco); border-bottom: 1px solid var(--borde); padding: .75rem 1.5rem; display: flex; align-items: center; gap: 1rem; position: sticky; top: 0; z-index: 1030; }
        .topbar h1 { font-size: 1.2rem; margin: 0; font-family: 'Playfair Display', Georgia, serif; color: var(--verde); }
        .pagina { padding: 1.5rem; }
        .btn { display: inline-block; padding: 0.5rem 1rem; background: var(--verde); color: #fff; text-decoration: none; border-radius: var(--radio); font-size: 0.9rem; font-weight: 600; }
        .btn:hover { background: var(--verde-2); }
        .actions { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; background: var(--blanco); border-radius: var(--radio); overflow: hidden; box-shadow: var(--sombra); border: 1px solid var(--borde); }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--borde); }
        th { background: var(--verde); color: #fff; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em; }
        tr:hover { background: #f9f9f9; }
    </style>
</head>
<body>
    <div class="sidebar" id="menuLateral">
        <div class="sidebar-logo">
            <div class="marca">Olivo Real</div>
            <div class="sub">RESTAURANT</div>
        </div>
        <nav>
            <a href="/dashboard" class="enlace">Dashboard</a>
            <a href="/usuarios" class="enlace activo">Usuarios</a>
            <a href="/mesas" class="enlace">Mesas</a>
            <a href="/logout" class="enlace" style="color:rgba(255,255,255,.5);">Cerrar sesión</a>
        </nav>
        <div class="sidebar-pie"></div>
    </div>
    <div class="contenido">
        <header class="topbar">
            <h1>Gestión de Usuarios</h1>
        </header>
        <main class="pagina">
            <h1 style="font-family:'Playfair Display',Georgia,serif; color:var(--verde); margin-bottom:1.5rem;">Usuarios · Olivo Real Restaurant</h1>
            <div class="actions">
                <a href="/usuarios/nuevo" class="btn">Nuevo Usuario</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; color:var(--gris);">No hay usuarios registrados.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= htmlspecialchars($usuario['id'] ?? '') ?></td>
                            <td><?= htmlspecialchars($usuario['nombre'] ?? '') ?></td>
                            <td><?= htmlspecialchars($usuario['email'] ?? $usuario['usuario'] ?? '') ?></td>
                            <td><?= htmlspecialchars($usuario['rol'] ?? $usuario['rol_nombre'] ?? $usuario['rol_id'] ?? '') ?></td>
                            <td><?= htmlspecialchars($usuario['estado'] ?? 'Activo') ?></td>
                            <td><a href="/usuarios/editar/<?= $usuario['id'] ?? '' ?>" class="btn" style="background:var(--verde-2); padding:.35rem .7rem; font-size:.8rem;">Editar</a></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
