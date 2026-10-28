<?php
// Vista: cocina.php
// Cocina - Órdenes en preparación - Olivo Real Restaurant.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cocina · Olivo Real Restaurant</title>
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
        .columnas-cocina { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
        .col-cocina { background: var(--blanco); border: 1px solid var(--borde); border-radius: var(--radio); padding: 1rem; height: 100%; min-height: 220px; box-shadow: var(--sombra); }
        .col-titulo { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
        .col-titulo strong { font-family: 'Playfair Display', Georgia, serif; color: var(--verde); }
        .conteo { font-weight: 700; color: var(--verde); background: #d5f5e3; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.9rem; }
        .items-col { list-style: none; padding: 0; margin: 0.4rem 0; font-size: 0.88rem; }
        .items-col li { padding: 0.2rem 0; border-bottom: 1px solid var(--borde); }
        .items-col li:last-child { border-bottom: none; }
        .items-col li:empty + .vacio, .vacio { color: var(--gris); font-style: italic; }
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
            <a href="/pedidos" class="enlace">Pedidos</a>
            <a href="/cocina" class="enlace activo">Cocina</a>
            <a href="/logout" class="enlace" style="color:rgba(255,255,255,.5);">Cerrar sesión</a>
        </nav>
        <div class="sidebar-pie"></div>
    </div>
    <div class="contenido">
        <header class="topbar">
            <h1>Cocina - Órdenes en Preparación</h1>
        </header>
        <main class="pagina">
            <h1 style="font-family:'Playfair Display',Georgia,serif; color:var(--verde); margin-bottom:1.5rem;">Cocina · Olivo Real Restaurant</h1>
            <div class="columnas-cocina">
                <div class="col-cocina">
                    <div class="col-titulo">
                        <strong>Pendientes</strong>
                        <span class="conteo"><?= count($pendientes ?? []) ?></span>
                    </div>
                    <ul class="items-col">
                        <?php if (empty($pendientes ?? [])): ?>
                        <li style="color:var(--gris);font-style:italic;">Sin órdenes pendientes</li>
                        <?php else: ?>
                            <?php foreach ($pendientes ?? [] as $orden): ?>
                            <li><strong>#<?= htmlspecialchars($orden['id'] ?? '') ?></strong> - Mesa <?= htmlspecialchars($orden['mesa'] ?? '') ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-cocina">
                    <div class="col-titulo">
                        <strong>En Preparación</strong>
                        <span class="conteo"><?= count($preparacion ?? []) ?></span>
                    </div>
                    <ul class="items-col">
                        <?php if (empty($preparacion ?? [])): ?>
                        <li style="color:var(--gris);font-style:italic;">Sin órdenes en preparación</li>
                        <?php else: ?>
                            <?php foreach ($preparacion ?? [] as $orden): ?>
                            <li><strong>#<?= htmlspecialchars($orden['id'] ?? '') ?></strong> - Mesa <?= htmlspecialchars($orden['mesa'] ?? '') ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-cocina">
                    <div class="col-titulo">
                        <strong>Listas</strong>
                        <span class="conteo"><?= count($lista ?? []) ?></span>
                    </div>
                    <ul class="items-col">
                        <?php if (empty($lista ?? [])): ?>
                        <li style="color:var(--gris);font-style:italic;">Sin órdenes listas</li>
                        <?php else: ?>
                            <?php foreach ($lista ?? [] as $orden): ?>
                            <li><strong>#<?= htmlspecialchars($orden['id'] ?? '') ?></strong> - Mesa <?= htmlspecialchars($orden['mesa'] ?? '') ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
