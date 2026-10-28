<?php
/**
 * Sesión y roles. Un solo sistema de sesión para todo el proyecto.
 * La sesión guarda: $_SESSION['usuario'] = ['id' => .., 'nombre' => .., 'rol' => ..]
 * El campo rol se almacena con la clave canónica (admin, mesero, cocina, cajero).
 */
class Auth
{
    const ROLES = [
        'admin'  => 'Administrador',
        'mesero' => 'Mesero',
        'cocina' => 'Cocina',
        'cajero' => 'Cajero',
    ];

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

    /**
     * Convierte ID, nombre amigable o clave interna a la clave canónica.
     * Ej.: 1, "Administrador", "SuperAdmin" → "admin"
     */
    public static function normalizarRol($rol): string
    {
        $clave = strtolower(trim((string) $rol));
        $clave = preg_replace('/[\s_\-]+/', '', $clave);

        $mapa = [
            '1'                   => 'admin',
            'admin'               => 'admin',
            'administrador'       => 'admin',
            'superadmin'          => 'admin',
            'superadministrador'  => 'admin',
            'gerente'             => 'admin',
            '2'                   => 'mesero',
            'mesero'              => 'mesero',
            '3'                   => 'cajero',
            'cajero'              => 'cajero',
            'caja'                => 'cajero',
            '4'                   => 'cocina',
            'cocina'              => 'cocina',
            'chef'                => 'cocina',
            'cocinero'            => 'cocina',
        ];

        return $mapa[$clave] ?? $clave;
    }

    /** Guarda en la sesión solo lo necesario. $u: ['id' => .., 'nombre' => .., 'rol' => ..] */
    public static function iniciarSesion(array $u)
    {
        session_regenerate_id(true);
        $rolCrudo = $u['rol'] ?? $u['rol_nombre'] ?? $u['rol_id'] ?? '';
        $_SESSION['usuario'] = [
            'id'     => $u['id'],
            'nombre' => $u['nombre'],
            'rol'    => self::normalizarRol($rolCrudo),
        ];
    }

    /** Entra con una cuenta ficticia. Devuelve false si el demo está apagado o el rol no existe. */
    public static function iniciarDemo($rol)
    {
        $rol = self::normalizarRol($rol);
        if (!defined('MODO_DEMO') || !MODO_DEMO || !isset(self::DEMO[$rol])) {
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
        if ($u === null) {
            return false;
        }
        $rolUsuario = self::normalizarRol($u['rol'] ?? '');
        $permitidos = array_map([self::class, 'normalizarRol'], $roles);
        return in_array($rolUsuario, $permitidos, true);
    }

    /**
     * Debe ir al inicio de cada controlador. Sin sesión → login. Con sesión pero sin permiso → 403.
     * No basta con ocultar el menú: esta es la validación real.
     */
    public static function exigirRol(array $roles)
    {
        if (self::usuario() === null) {
            redirigir('/login');
        }
        if (!self::tieneRol($roles)) {
            http_response_code(403);
            echo 'Acceso denegado.';
            exit;
        }
    }
}
