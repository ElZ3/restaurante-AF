<?php
// Vista: usuarios.php
// Gestión de usuarios del sistema.
// Renderizada directamente durante el maquetado frontend.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Restaurate AF</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; color: #333; }
        header { background: #2c3e50; color: #fff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; text-decoration: none; margin-left: 1.5rem; font-weight: bold; }
        nav a:hover { text-decoration: underline; }
        main { padding: 2rem; max-width: 1200px; margin: 0 auto; }
        h1 { margin-bottom: 1.5rem; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #2c3e50; color: #fff; font-weight: bold; }
        tr:hover { background: #f9f9f9; }
        .btn { display: inline-block; padding: 0.5rem 1rem; background: #27ae60; color: #fff; text-decoration: none; border-radius: 4px; font-size: 0.9rem; }
        .btn:hover { background: #219150; }
        .actions { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <header>
        <div>Gestión de Usuarios</div>
        <nav>
            <a href="/dashboard">Dashboard</a>
            <a href="/mesas">Mesas</a>
            <a href="/logout">Cerrar sesión</a>
        </nav>
    </header>
    <main>
        <h1>Usuarios del Sistema</h1>
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
                        <td colspan="6" style="text-align: center;">No hay usuarios registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= htmlspecialchars($usuario['id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($usuario['nombre'] ?? '') ?></td>
                        <td><?= htmlspecialchars($usuario['usuario'] ?? '') ?></td>
                        <td><?= htmlspecialchars($usuario['rol'] ?? '') ?></td>
                        <td><?= htmlspecialchars($usuario['estado'] ?? 'Activo') ?></td>
                        <td>
                            <a href="/usuarios/editar/<?= $usuario['id'] ?? '' ?>" class="btn" style="background:#2c3e50;">Editar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
