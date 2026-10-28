<?php
require __DIR__ . '/app/bootstrap.php';

if (Auth::usuario()) {
    redirigir('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validar($_POST['csrf'] ?? '')) {
        $error = 'El formulario expiró. Intenta de nuevo.';
    } elseif (isset($_POST['demo'])) {
        // Modo demo: sin base de datos.
        if (Auth::iniciarDemo($_POST['demo_rol'] ?? '')) {
            redirigir('dashboard.php');
        }
        $error = 'El modo demo no está disponible.';
    } else {
        // BACKEND: Usuario::autenticar() valida con password_verify(), limita intentos y devuelve el usuario.
        $usuario = Usuario::autenticar(trim($_POST['correo'] ?? ''), (string) ($_POST['clave'] ?? ''));
        if ($usuario) {
            Auth::iniciarSesion($usuario);
            redirigir('dashboard.php');
        }
        $error = 'Correo o contraseña incorrectos.';
    }
}

render('auth/login', ['error' => $error, 'modo_demo' => MODO_DEMO], false);
