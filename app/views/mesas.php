<?php
// Vista: mesas.php
// Gestión de mesas del restaurante.
// Renderizada directamente durante el maquetado frontend.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesas - Restaurate AF</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; color: #333; }
        header { background: #2c3e50; color: #fff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; text-decoration: none; margin-left: 1.5rem; font-weight: bold; }
        nav a:hover { text-decoration: underline; }
        main { padding: 2rem; max-width: 1200px; margin: 0 auto; }
        h1 { margin-bottom: 1.5rem; color: #2c3e50; }
        .mesas-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 1rem; }
        .mesa { background: #fff; border-radius: 8px; padding: 1.5rem; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border: 2px solid #27ae60; }
        .mesa.ocupada { border-color: #e74c3c; }
        .mesa h3 { margin-bottom: 0.5rem; color: #2c3e50; font-size: 1.2rem; }
        .mesa .estado { font-size: 0.85rem; padding: 0.25rem 0.5rem; border-radius: 4px; display: inline-block; margin-top: 0.5rem; }
        .disponible { background: #d5f5e3; color: #27ae60; }
        .ocupada { background: #fadbd8; color: #e74c3c; }
        .btn { display: inline-block; margin-top: 1rem; padding: 0.5rem 1rem; background: #2c3e50; color: #fff; text-decoration: none; border-radius: 4px; font-size: 0.9rem; }
        .btn:hover { background: #1a252f; }
        .actions { margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <header>
        <div>Gestión de Mesas</div>
        <nav>
            <a href="/dashboard">Dashboard</a>
            <a href="/usuarios">Usuarios</a>
            <a href="/logout">Cerrar sesión</a>
        </nav>
    </header>
    <main>
        <h1>Mesas del Restaurante</h1>
        <div class="actions">
            <a href="/mesas/nueva" class="btn">Nueva Mesa</a>
        </div>
        <div class="mesas-grid">
            <?php if (empty($mesas)): ?>
                <div style="grid-column: 1 / -1; text-align: center; color: #777;">No hay mesas registradas.</div>
            <?php else: ?>
                <?php foreach ($mesas as $mesa): ?>
                <div class="mesa <?= ($mesa['estado'] ?? '') === 'ocupada' ? 'ocupada' : '' ?>">
                    <h3>Mesa <?= htmlspecialchars($mesa['numero'] ?? '') ?></h3>
                    <span class="estado <?= ($mesa['estado'] ?? '') === 'ocupada' ? 'ocupada' : 'disponible' ?>">
                        <?= ucfirst(htmlspecialchars($mesa['estado'] ?? 'disponible')) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
