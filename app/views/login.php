<?php
// Vista: login.php
// Página de inicio de sesión del sistema de restaurante.
// Renderizada directamente durante el maquetado frontend (sesión comentada).
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Restaurate AF</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-card { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #2c3e50; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; color: #333; }
        input[type="text"], input[type="password"] { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 4px; }
        button { width: 100%; padding: 0.75rem; background: #2c3e50; color: #fff; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; }
        button:hover { background: #1a252f; }
        .error { color: #e74c3c; margin-bottom: 1rem; text-align: center; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Iniciar Sesión</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="/login" method="POST">
            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" required>
            <label for="contrasena">Contraseña</label>
            <input type="password" id="contrasena" name="contrasena" required>
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>
