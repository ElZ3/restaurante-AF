<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar sesión · Olivo Real</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link href="css/estilos.css" rel="stylesheet">
  <style>
    /* Imagen de login (img/login.jpg) con una capa verde para que el texto se lea bien.
       Si la imagen no existe, se ve el degradado verde de siempre. */
    .login-imagen {
      background:
        linear-gradient(160deg, rgba(24,61,52,.55), rgba(12,35,29,.85)),
        url('img/login.jpg') center / cover no-repeat,
        linear-gradient(160deg, #183D34, #0c231d);
    }
    .login-imagen .marca, .login-imagen .sub, .login-imagen .lema { text-shadow: 0 2px 12px rgba(0,0,0,.45); }
    .login-logo { display: block; max-width: 220px; max-height: 110px; width: auto; height: auto; margin: 0 auto 1.5rem; }
  </style>
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
      <img src="img/logo.png" alt="Olivo Real" class="login-logo" onerror="this.style.display='none'">
      <h2 class="mb-1">Iniciar sesión</h2>
      <p class="text-secondary mb-4">Accede a tu cuenta para continuar</p>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2 small" role="alert"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" action="login.php" novalidate>
        <?= Csrf::campo() ?>
        <div class="mb-3">
          <label class="form-label" for="correo">Correo electrónico</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" id="correo" name="correo" placeholder="correo@olivoreal.com" required autofocus>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="clave">Contraseña</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="clave" name="clave" placeholder="••••••••" required>
            <button class="btn btn-outline-secondary" type="button" id="verClave" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
          </div>
        </div>
        <button type="submit" class="btn btn-olivo w-100 py-2 mt-2">Ingresar <i class="bi bi-arrow-right ms-1"></i></button>
        <?php if ($modo_demo): ?>
        <!-- MODO DEMO: se apaga con MODO_DEMO = false en app/bootstrap.php cuando exista el backend -->
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
  document.getElementById('verClave').addEventListener('click', () => {
    const c = document.getElementById('clave');
    c.type = c.type === 'password' ? 'text' : 'password';
  });
</script>
</body>
</html>