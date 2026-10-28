<?php
/**
 * OLIVO REAL · Arranque común. Cada controlador (login.php, reservas.php, ...) empieza con:
 *     require __DIR__ . '/app/bootstrap.php';
 */
define('APP_PATH', __DIR__);

// Modo demo: permite entrar sin base de datos, eligiendo un rol en el login.
// PONER EN false al conectar la base de datos y antes de subir al servidor real.
const MODO_DEMO = true;

// Carga automática: new Reserva / Auth::... buscan el archivo en app/core y app/models.
spl_autoload_register(function ($clase) {
    foreach (['core', 'models'] as $carpeta) {
        $archivo = APP_PATH . '/' . $carpeta . '/' . $clase . '.php';
        if (is_file($archivo)) {
            require $archivo;
            return;
        }
    }
});

require APP_PATH . '/core/helpers.php';

Auth::iniciar();
