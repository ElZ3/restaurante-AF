<?php
// Vista: dashboard.php
// Panel principal del sistema de restaurante.
// Renderizada directamente durante el maquetado frontend.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Restaurate AF</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; color: #333; }
        header { background: #2c3e50; color: #fff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; text-decoration: none; margin-left: 1.5rem; font-weight: bold; }
        nav a:hover { text-decoration: underline; }
        main { padding: 2rem; max-width: 1200px; margin: 0 auto; }
        h1 { margin-bottom: 1.5rem; color: #2c3e50; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card h3 { color: #2c3e50; margin-bottom: 0.5rem; }
        .stat-card .valor { font-size: 2rem; font-weight: bold; color: #27ae60; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
        .card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h3 { margin-bottom: 1rem; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
    </style>
</head>
<body>
    <header>
        <div>Restaurate AF - Dashboard</div>
        <nav>
            <a href="/mesas">Mesas</a>
            <a href="/usuarios">Usuarios</a>
            <a href="/logout">Cerrar sesión</a>
        </nav>
    </header>
    <main>
        <h1>Panel de Control</h1>
        <div class="stats">
            <div class="stat-card">
                <h3>Mesas Totales</h3>
                <div class="valor">--</div>
            </div>
            <div class="stat-card">
                <h3>Mesas Ocupadas</h3>
                <div class="valor">--</div>
            </div>
            <div class="stat-card">
                <h3>Pedidos Hoy</h3>
                <div class="valor">--</div>
            </div>
            <div class="stat-card">
                <h3>Usuarios</h3>
                <div class="valor">--</div>
            </div>
        </div>
        <div class="grid">
            <div class="card">
                <h3>Acciones Rápidas</h3>
                <p>Gestiona mesas, pedidos y usuarios desde el menú superior.</p>
            </div>
        </div>
    </main>
</body>
</html>
