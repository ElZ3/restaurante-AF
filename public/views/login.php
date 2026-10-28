<?php
$error = !empty($_GET['error']) ? 'Usuario o contraseña incorrectos.' : '';
$modo_demo = !empty($modo_demo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar sesión · Olivo Real</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link href="/css/estilos.css" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
  <section class="login-imagen">
    <div class="marca">Olivo Real</div>
    <div class="sub">RESTAURANTE</div>
    <p class="lema">El arte de compartir una buena mesa.</p>
  </section>
  <section class="login-form">
    <div class="login-caja">
      <img src="/img/logo.png" alt="Olivo Real" class="login-logo">
      <h1 class="h2 mb-1">Iniciar sesión</h1>
      <p class="text-secondary mb-4">Accede a tu cuenta para continuar</p>
      <?php if ($error !== ''): ?>
        <div class="alert alert-danger py-2 small" role="alert"><?= e($error) ?></div>
      <?php endif; ?>
      <?php if (!empty($_GET['demo'])): ?>
        <div class="alert alert-warning py-2 small" role="alert">El modo demo está desactivado.</div>
      <?php endif; ?>
      <form method="post" action="/autenticar">
        <?= Csrf::campo() ?>
        <div class="mb-3">
          <label class="form-label" for="usuario">Correo electrónico</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-envelope" aria-hidden="true"></i></span>
            <input type="text" class="form-control" id="usuario" name="usuario" autocomplete="username" placeholder="correo@olivoreal.com" required autofocus>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="contrasena">Contraseña</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-lock" aria-hidden="true"></i></span>
            <input type="password" class="form-control" id="contrasena" name="contrasena" autocomplete="current-password" placeholder="••••••••" required>
            <button class="btn btn-outline-secondary" type="button" id="verClave" aria-label="Mostrar contraseña"><i class="bi bi-eye" aria-hidden="true"></i></button>
          </div>
        </div>
        <button type="submit" class="btn btn-olivo w-100 py-2 mt-2">Ingresar <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></button>
        <?php if ($modo_demo): ?>
          <div class="border-top mt-3 pt-3">
            <label class="form-label small text-secondary" for="demoRol">Modo demo: entrar como</label>
            <div class="input-group">
              <select class="form-select" id="demoRol" name="demo_rol">
                <?php foreach (Auth::ROLES as $clave => $texto): ?>
                  <option value="<?= e($clave) ?>"><?= e($texto) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" name="demo" value="1" formnovalidate class="btn btn-outline-olivo">Entrar</button>
            </div>
          </div>
        <?php endif; ?>
      </form>
    </div>
  </section>
</div>
<script>
  document.getElementById('verClave').addEventListener('click', function () {
    const campo = document.getElementById('contrasena');
    const mostrar = campo.type === 'password';
    campo.type = mostrar ? 'text' : 'password';
    this.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
  });
</script>
</body>
</html>
