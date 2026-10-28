<?php
// Vista: login.php
// Página de inicio de sesión - Olivo Real Restaurant.
// Renderizada directamente durante el maquetado frontend.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · Olivo Real Restaurant</title>
    <link rel="stylesheet" href="/css/estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', Arial, sans-serif; background: var(--marfil); color: var(--texto); display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        :root {
          --verde: #183D34;
          --verde-2: #225247;
          --dorado: #D8BD82;
          --dorado-osc: #B99A56;
          --marfil: #F7F3EA;
          --blanco: #FFFFFF;
          --texto: #2E2E2E;
          --gris: #6B7280;
          --borde: #E5E1D6;
          --radio: 12px;
          --sombra: 0 1px 3px rgba(24,61,52,.08), 0 4px 14px rgba(24,61,52,.05);
        }
        .login-card { background: var(--blanco); padding: 2.5rem; border-radius: var(--radio); box-shadow: var(--sombra); width: 100%; max-width: 400px; border: 1px solid var(--borde); }
        .login-marca { font-family: 'Playfair Display', Georgia, serif; font-size: 2rem; font-weight: 700; color: var(--verde); text-align: center; margin-bottom: 0.25rem; }
        .login-sub { text-align: center; color: var(--dorado-osc); font-size: 0.85rem; letter-spacing: 0.3em; margin-bottom: 2rem; text-transform: uppercase; }
        h2 { text-align: center; margin-bottom: 1.5rem; color: var(--verde); font-family: 'Playfair Display', Georgia, serif; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--texto); font-size: 0.85rem; }
        input[type="text"], input[type="password"] { width: 100%; padding: 0.75rem 1rem; margin-bottom: 1rem; border: 1px solid var(--borde); border-radius: var(--radio); font-family: 'Inter', sans-serif; }
        button { width: 100%; padding: 0.75rem; background: var(--verde); color: #fff; border: none; border-radius: var(--radio); font-size: 1rem; cursor: pointer; font-weight: 600; }
        button:hover { background: var(--verde-2); }
        .error { color: #c62828; margin-bottom: 1rem; text-align: center; font-size: 0.85rem; background: #ffebee; padding: 0.75rem; border-radius: var(--radio); border: 1px solid #ef9a9a; }
        .login-pie { text-align: center; margin-top: 1.5rem; font-size: 0.8rem; color: var(--gris); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-marca">Olivo Real</div>
        <div class="login-sub">RESTAURANT</div>
        <h2>Iniciar sesión</h2>
        <?php if (isset($error) || isset($_GET['error'])): ?>
            <div class="error"><?= htmlspecialchars($error ?? 'Usuario o contraseña incorrectos.') ?></div>
        <?php endif; ?>
        <form action="/autenticar" method="POST">
            <label for="usuario">Correo / Usuario</label>
            <input type="text" id="usuario" name="usuario" required>
            <label for="contrasena">Contraseña</label>
            <input type="password" id="contrasena" name="contrasena" required>
            <button type="submit">Ingresar</button>
        </form>
        <div class="login-pie">Olivo Real Restaurant &mdash; El arte de compartir una buena mesa.</div>
    </div>
</body>
</html>
