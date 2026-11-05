<?php
/** Escapar salida HTML: usar siempre e($valor) al imprimir datos. */
if (!function_exists('e')) {
    function e($texto)
    {
        return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
    }
}

/** Redirige y termina el script. Rutas legacy .php se mapean al Front Controller. */
function redirigir($url)
{
    $legacy = [
        'login.php'     => '/login',
        '/login.php'    => '/login',
        'dashboard.php' => '/dashboard',
        '/dashboard.php'=> '/dashboard',
    ];
    if (isset($legacy[$url])) {
        $url = $legacy[$url];
    }
    header('Location: ' . $url);
    exit;
}

/** Responde JSON (peticiones Fetch/AJAX de modales y acciones dinámicas). */
function respuestaJson(array $payload, int $codigo = 200, array $extra = []): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    $payload = array_merge($payload, $extra);
    // Claves en español ({ exito, mensaje }) además de success/message, que usa el frontend actual.
    if (array_key_exists('success', $payload) && !array_key_exists('exito', $payload)) {
        $payload['exito'] = (bool) $payload['success'];
    }
    if (array_key_exists('message', $payload) && !array_key_exists('mensaje', $payload)) {
        $payload['mensaje'] = $payload['message'];
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Dibuja una vista dentro de la plantilla (encabezado + menú + pie) o solo la vista.
 *   render('reservas/index', ['titulo' => 'Reservas', 'pagina' => 'reservas', 'reservas' => $lista], true, 'Reservas', 'reservas');
 * Con el cuarto parámetro false se dibuja la vista sola, sin plantilla (la usa el login).
 * Las claves del arreglo se convierten en variables dentro de la vista.
 */
function render($__vista, array $__datos = [], $__conLayout = true, string $__titulo = '', string $__pagina = '')
{
    extract($__datos, EXTR_SKIP);
    $__archivo = PUBLIC_PATH . 'views/' . $__vista . '.php';

    if (!$__conLayout) {
        require $__archivo;
        return;
    }

    ob_start();
    require $__archivo;                       // la vista puede definir $scripts_extra
    $__contenido = ob_get_clean();

    render_app_layout(
        $__titulo !== '' ? $__titulo : basename($__vista),
        $__pagina !== '' ? $__pagina : basename($__vista),
        $__contenido,
        isset($scripts_extra) ? (string) $scripts_extra : ''
    );
}

// ---------------------------------------------------------------------------
// Validaciones de formato compartidas por los controladores.
// Deben coincidir con las reglas de public/js/sistema.js (objeto Validar).
// ---------------------------------------------------------------------------

/** Teléfono salvadoreño: +503 XXXX-XXXX (inicia en 2, 6 o 7). */
const REGEX_TELEFONO_SV = '/^\+503\s[267]\d{3}-\d{4}$/';

/** Nombres: solo letras (incluye acentos, ü y ñ) y espacios simples entre palabras. */
const REGEX_NOMBRE = '/^\p{L}+(?: \p{L}+)*$/u';

/** Quita espacios sobrantes (inicio, fin y repetidos). */
function normalizarEspacios(string $texto): string
{
    return trim((string) preg_replace('/\s+/u', ' ', $texto));
}

function esNombreValido(string $nombre, int $max = 100): bool
{
    return $nombre !== ''
        && mb_strlen($nombre) <= $max
        && preg_match(REGEX_NOMBRE, $nombre) === 1;
}

function esTelefonoSvValido(string $telefono): bool
{
    return preg_match(REGEX_TELEFONO_SV, $telefono) === 1;
}

/** Entero estrictamente mayor que 0 (no acepta "2.5", "-1", "0", "abc"). Devuelve null si no es válido. */
function enteroPositivo($valor, int $max = PHP_INT_MAX): ?int
{
    $n = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => $max]]);
    return $n === false ? null : (int) $n;
}

/** Decimal estrictamente mayor que 0 con máximo 2 decimales. Devuelve null si no es válido. */
function decimalPositivo($valor, float $max = 99999999.99): ?float
{
    $texto = trim((string) $valor);
    if (!preg_match('/^\d+(\.\d{1,2})?$/', $texto)) {
        return null;
    }
    $n = (float) $texto;
    return ($n > 0 && $n <= $max) ? $n : null;
}
