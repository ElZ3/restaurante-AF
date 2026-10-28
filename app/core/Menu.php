<?php
/**
 * Menú lateral y, al mismo tiempo, la tabla de quién puede entrar a cada pantalla.
 * Los controladores usan:  Auth::exigirRol(Menu::roles('reservas'));
 * Así el menú y la protección no se pueden desincronizar.
 *
 * Las claves de rol son canónicas (admin, mesero, cocina, cajero).
 * Auth::normalizarRol() acepta también "Administrador", "SuperAdmin", "caja", IDs, etc.
 */
class Menu
{
    // clave => [texto, icono, ruta amigable, roles que la ven y pueden entrar]
    public static function items()
    {
        return [
          'dashboard' => ['Inicio',         'bi-house-door',    '/dashboard',     ['admin','mesero','cocina','cajero']],
          'reservas'  => ['Reservas',       'bi-calendar-check', '/reservas',      ['admin','mesero','cajero']],
          'mesas'     => ['Mesas y áreas',  'bi-grid-3x3-gap',  '/mesas',         ['admin','mesero','cajero']],
          'pedidos'   => ['Pedidos',        'bi-receipt',       '/pedidos',       ['admin','mesero','cocina','cajero']],
          'cocina'    => ['Cocina',         'bi-fire',          '/cocina',        ['admin','cocina']],
          'menu'      => ['Menú',           'bi-book',          '/menu',          ['admin','mesero','cocina']],
          'cuentas'   => ['Cuentas y pagos','bi-cash-coin',     '/cuentas',       ['admin','cajero']],
          'reportes'  => ['Reportes',       'bi-bar-chart-line','/reportes',      ['admin','cajero']],
          'usuarios'  => ['Usuarios',       'bi-people',        '/usuarios',      ['admin']],
          'config'    => ['Configuración',  'bi-gear',          '/configuracion', ['admin']],
        ];
    }

    public static function roles($clave)
    {
        $items = self::items();
        return isset($items[$clave]) ? $items[$clave][3] : [];
    }
}
