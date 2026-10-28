<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoloader primero: sin salida de texto, compatible con Linux (case-sensitive).
spl_autoload_register(function ($class) {
    $classClean = ltrim($class, '\\');
    $classPath = str_replace('\\', '/', $classClean);
    $relativePath = preg_replace('/^App\//i', '', $classPath);
    $className = basename($relativePath);

    $baseDir = dirname(__DIR__) . '/app/';

    $possibleFiles = [
        $baseDir . $relativePath . '.php',
        $baseDir . strtolower(dirname($relativePath)) . '/' . $className . '.php',
        $baseDir . 'controllers/' . $className . '.php',
        $baseDir . 'models/' . $className . '.php',
        $baseDir . 'core/' . $className . '.php',
        $baseDir . $className . '.php',
    ];

    foreach ($possibleFiles as $file) {
        if (is_file($file)) {
            require_once $file;
            if (!class_exists($class, false) && class_exists($className, false)) {
                class_alias($className, $class);
            }
            return;
        }
    }

    foreach (['controllers', 'models', 'core'] as $dir) {
        $folder = $baseDir . $dir;
        if (!is_dir($folder)) {
            continue;
        }
        foreach (scandir($folder) as $entry) {
            if (strcasecmp($entry, $className . '.php') !== 0) {
                continue;
            }
            require_once $folder . '/' . $entry;
            if (!class_exists($class, false) && class_exists($className, false)) {
                class_alias($className, $class);
            }
            return;
        }
    }
});

// Cargar helpers y Auth (clases globales, sin namespace). Sin echo/BOM previo a la sesión.
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/Auth.php';

if (!class_exists('App\\Core\\Auth', false) && class_exists('Auth', false)) {
    class_alias('Auth', 'App\\Core\\Auth');
}

Auth::iniciar();
// Enrutador principal del sistema Olivo Real Restaurant.
// Todo el tráfico pasa por este archivo (Front Controller).
// Implementa el patrón MVC: rutas -> controladores -> vistas.

// Definir la URL base y limpiar la ruta solicitada.
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestUri = trim((string) $requestUri, '/');

// Mapa de rutas estáticas: ruta -> [controlador, acción]
$rutasEstaticas = [
    ''         => ['LoginController',    'index'],
    'login'    => ['LoginController',    'index'],
    'autenticar'=> ['LoginController',   'autenticar'],
    'logout'   => ['LoginController',    'logout'],
    'dashboard'=> ['DashboardController', 'index'],
    'usuarios' => ['UsuarioController',  'index'],
    'mesas'    => ['MesaController',     'index'],
    'menu'     => null, // se renderiza vista directamente
    'pedidos'  => null, // se renderiza vista directamente
    'cocina'   => null, // se renderiza vista directamente
    'reportes' => null, // se renderiza vista directamente
    'cuentas'  => null, // se renderiza vista directamente
];

// Rutas con parámetros dinámicos: patrón -> [controlador, acción]
// Los segmentos :id se capturan y se pasan como parámetro a la acción.
$rutasParametrizadas = [
    'usuarios/nuevo'      => ['UsuarioController',  'nuevo'],
    'usuarios/crear'      => ['UsuarioController',  'crear'],
    'usuarios/editar/:id' => ['UsuarioController',  'editar'],
    'usuarios/actualizar/:id' => ['UsuarioController', 'actualizar'],
    'usuarios/eliminar/:id' => ['UsuarioController', 'eliminar'],
    'mesas/nueva'         => ['MesaController',     'nuevo'],
    'mesas/crear'         => ['MesaController',     'crear'],
    'mesas/editar/:id'    => ['MesaController',     'editar'],
    'mesas/actualizar/:id'=> ['MesaController',     'actualizar'],
    'mesas/cambiar-estado/:id' => ['MesaController', 'cambiarEstado'],
    'mesas/eliminar/:id'  => ['MesaController',     'eliminar'],
];

// Resolver controlador, acción y parámetros.
$controlador = null;
$accion      = null;
$parametros  = [];
$vista       = null;

// 1. Intentar coincidencia estática.
if (isset($rutasEstaticas[$requestUri])) {
    [$controlador, $accion] = $rutasEstaticas[$requestUri];
}
// 2. Intentar coincidencia parametrizada.
else {
    foreach ($rutasParametrizadas as $patron => $ruta) {
        $patronRegex = '#^' . str_replace('/', '\/', $patron) . '$#';
        $patronRegex = str_replace(':id', '([0-9]+)', $patronRegex);
        if (preg_match($patronRegex, $requestUri, $matches)) {
            array_shift($matches); // eliminar el match completo
            [$controlador, $accion] = $ruta;
            $parametros = $matches;
            break;
        }
    }
}

// Si no se encontró ninguna ruta, intentar renderizar vista directamente.
if ($controlador === null) {
    // Ruta sin controlador: renderizar vista directamente por nombre.
    $vista = $requestUri === '' ? 'login' : $requestUri;
}

// Instanciar controlador y ejecutar acción si corresponde.
if ($controlador !== null) {
    $claseControlador = 'App\\Controllers\\' . $controlador;
    $rutaArchivo      = __DIR__ . '/../app/controllers/' . $controlador . '.php';

    if (file_exists($rutaArchivo)) {
        require_once $rutaArchivo;
        if (!class_exists($claseControlador, false) && class_exists($controlador, false)) {
            class_alias($controlador, $claseControlador);
        }
        $claseReal = class_exists($claseControlador, false) ? $claseControlador : $controlador;
        $controladorInstancia = new $claseReal();

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
// Las vistas ahora residen en public/views/ (misma carpeta que el front controller).
if ($vista !== null) {
    $vistaArchivo = __DIR__ . '/views/' . $vista . '.php';
    if (file_exists($vistaArchivo)) {
        require_once $vistaArchivo;
        exit;
    }
}

// Vista por defecto si no se resuelve nada.
header('Location: /login', true, 302);
exit;
