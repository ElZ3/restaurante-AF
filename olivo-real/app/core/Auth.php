<?php
/**
 * Sesión y roles. Un solo sistema de sesión para todo el proyecto.
 * La sesión guarda: $_SESSION['usuario'] = ['id' => .., 'nombre' => .., 'rol' => ..]
 */
class Auth
{
    const ROLES = ['admin' => 'Administrador', 'mesero' => 'Mesero', 'cocina' => 'Cocina', 'cajero' => 'Cajero'];

    // Cuentas ficticias del modo demo (solo se usan si MODO_DEMO es true).
    const DEMO = [
        'admin'  => ['id' => 1, 'nombre' => 'Ana López'],
        'mesero' => ['id' => 2, 'nombre' => 'Carlos Méndez'],
        'cocina' => ['id' => 4, 'nombre' => 'José Ramírez'],
        'cajero' => ['id' => 5, 'nombre' => 'Valeria Torres'],
    ];

    public static function iniciar()
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }
        session_name('OLIVOSESS');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    /** Usuario con sesión iniciada, o null. */
    public static function usuario()
    {
        return isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
    }

    /** Guarda en la sesión solo lo necesario. $u: ['id' => .., 'nombre' => .., 'rol' => ..] */
    public static function iniciarSesion(array $u)
    {
        session_regenerate_id(true);          // evita la fijación de sesión
        $_SESSION['usuario'] = ['id' => $u['id'], 'nombre' => $u['nombre'], 'rol' => $u['rol']];
    }

    /** Entra con una cuenta ficticia. Devuelve false si el demo está apagado o el rol no existe. */
    public static function iniciarDemo($rol)
    {
        if (!MODO_DEMO || !isset(self::DEMO[$rol])) {
            return false;
        }
        self::iniciarSesion(self::DEMO[$rol] + ['rol' => $rol]);
        return true;
    }

    public static function cerrar()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $c = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $c['path'], $c['domain'], $c['secure'], $c['httponly']);
        }
        session_destroy();
    }

    public static function tieneRol(array $roles)
    {
        $u = self::usuario();
        return $u !== null && in_array($u['rol'], $roles, true);
    }

    /**
     * Debe ir al inicio de cada controlador. Sin sesión → login. Con sesión pero sin permiso → 403.
     * No basta con ocultar el menú: esta es la validación real.
     */
    public static function exigirRol(array $roles)
    {
        if (self::usuario() === null) {
            redirigir('login.php');
        }
        if (!self::tieneRol($roles)) {
            http_response_code(403);
            render('errores/403', ['titulo' => 'Sin permiso', 'pagina' => '']);
            exit;
        }
    }
}
