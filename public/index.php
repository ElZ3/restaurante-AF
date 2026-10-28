<?php
// Enrutador principal del sistema Restaurate AF.
// Todo el tráfico pasa por este archivo (Front Controller).
// Implementa el patrón MVC: rutas -> controladores -> vistas.

// Iniciar sesión (comentado temporalmente para maquetado frontend).
// session_start();

// Definir la URL base y limpiar la ruta solicitada.
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestUri = rtrim($requestUri, '/');

// Mapa de rutas: ruta -> [controlador, acción]
$rutas = [
    ''         => ['LoginController',    'index'],
    'login'    => ['LoginController',    'index'],
    'autenticar'=> ['LoginController',   'autenticar'],
    'logout'   => ['LoginController',    'logout'],
    'dashboard'=> ['UsuarioController',  'index'], // placeholder temporal
    'usuarios' => ['UsuarioController',  'index'],
    'mesas'    => null, // se renderiza vista directamente (sin controlador propio aún)
];

// Resolver controlador y acción.
$controlador = null;
$accion      = null;
$parametros  = [];

if (isset($rutas[$requestUri])) {
    $ruta = $rutas[$requestUri];
    if ($ruta === null) {
        // Ruta sin controlador: renderizar vista directamente.
        $vista = $requestUri === '' ? 'login' : $requestUri;
    } else {
        [$controlador, $accion] = $ruta;
    }
} else {
    // Ruta no encontrada: redirigir a login o mostrar error 404.
    header('Location: /login', true, 302);
    exit;
}

// Instanciar controlador y ejecutar acción si corresponde.
if ($controlador !== null) {
    $claseControlador = 'App\\Controllers\\' . $controlador;
    $rutaArchivo      = __DIR__ . '/../app/controllers/' . $controlador . '.php';

    if (file_exists($rutaArchivo)) {
        require_once $rutaArchivo;
        $controladorInstancia = new $claseControlador();

        if (method_exists($controladorInstancia, $accion)) {
            // Llamar a la acción con parámetros de ruta si los hay.
            if (!empty($parametros)) {
                $controladorInstancia->$accion(...$parametros);
            } else {
                $controladorInstancia->$accion();
            }
            exit;
        }
    }
}

// Renderizar vista directamente cuando no hay controlador asociado.
$vistaArchivo = __DIR__ . '/../app/views/' . $vista . '.php';
if (file_exists($vistaArchivo)) {
    require_once $vistaArchivo;
    exit;
}

// Vista por defecto si no se resuelve nada.
header('Location: /login', true, 302);
exit;
