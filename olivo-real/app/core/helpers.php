<?php
/** Escapar salida HTML: usar siempre e($valor) al imprimir datos. */
if (!function_exists('e')) {
    function e($texto)
    {
        return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
    }
}

/** Redirige y termina el script. */
function redirigir($url)
{
    header('Location: ' . $url);
    exit;
}

/**
 * Dibuja una vista de app/views/ dentro de la plantilla (encabezado + menú + pie).
 *   render('reservas/lista', ['titulo' => 'Reservas', 'pagina' => 'reservas', 'reservas' => $lista]);
 * Con un tercer parámetro false se dibuja la vista sola, sin plantilla (la usa el login).
 * Las claves del arreglo se convierten en variables dentro de la vista.
 */
function render($__vista, array $__datos = [], $__conLayout = true)
{
    // Los parámetros llevan "__" para que no choquen con variables de las vistas (p. ej. $datos).
    extract($__datos, EXTR_SKIP);
    $__archivo = APP_PATH . '/views/' . $__vista . '.php';

    if (!$__conLayout) {
        require $__archivo;
        return;
    }

    ob_start();
    require $__archivo;                       // la vista puede definir $scripts_extra
    $__contenido = ob_get_clean();

    require APP_PATH . '/views/layout/encabezado.php';
    echo $__contenido;
    require APP_PATH . '/views/layout/pie.php';
}
