<?php
/**
 * Menú lateral y, al mismo tiempo, la tabla de quién puede entrar a cada pantalla.
 * Los controladores usan:  Auth::exigirRol(Menu::roles('reservas'));
 * Así el menú y la protección no se pueden desincronizar.
 */
class Menu
{
    // clave => [texto, icono, archivo, roles que la ven y pueden entrar]
    public static function items()
    {
        return [
          'dashboard' => ['Inicio',        'bi-house-door',   'dashboard.php', ['admin','mesero','cocina','cajero']],
          'reservas'  => ['Reservas',      'bi-calendar-check','reservas.php',  ['admin','mesero','cajero']],
          'mesas'     => ['Mesas y áreas', 'bi-grid-3x3-gap', 'mesas.php',     ['admin','mesero','cajero']],
          'pedidos'   => ['Pedidos',       'bi-receipt',      'pedidos.php',   ['admin','mesero','cocina','cajero']],
          'cocina'    => ['Cocina',        'bi-fire',         'cocina.php',    ['admin','cocina']],
          'menu'      => ['Menú',          'bi-book',         'menu.php',      ['admin','mesero','cocina']],
          'cuentas'   => ['Cuentas y pagos','bi-cash-coin',   'cuentas.php',   ['admin','cajero']],
          'reportes'  => ['Reportes',      'bi-bar-chart-line','reportes.php',  ['admin','cajero']],
          'usuarios'  => ['Usuarios',      'bi-people',       'usuarios.php',  ['admin']],
          'config'    => ['Configuración', 'bi-gear',         'configuracion.php', ['admin']],
        ];
    }

    public static function roles($clave)
    {
        $items = self::items();
        return isset($items[$clave]) ? $items[$clave][3] : [];
    }
}
