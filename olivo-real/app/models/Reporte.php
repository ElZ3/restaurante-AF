<?php
/**
 * Reportes.
 * BACKEND: los reportes se calcularán con consultas SQL. "Ventas" = solo cuentas pagadas (tabla pagos).
 * Por ahora los datos de demostración se generan de forma fija en JavaScript, a partir de estos productos.
 */
class Reporte
{
    // Admin ve todo; el cajero solo ventas y pagos (acceso parcial).
    public static function tipos($rol)
    {
        $tipos = ['ventas' => 'Ventas por período', 'productos' => 'Productos más vendidos', 'pedidos' => 'Pedidos atendidos',
                  'reservas' => 'Reservas', 'pagos' => 'Pagos'];
        if ($rol === 'cajero') {
            $tipos = array_intersect_key($tipos, ['ventas' => 1, 'pagos' => 1]);
        }
        return $tipos;
    }

    // [id, nombre, precio]
    public static function productos()
    {
        return array_map(function ($p) {
            return [$p['id'], $p['n'], $p['p']];
        }, Producto::paraPedidos());
    }

    // Los últimos 30 días, del más antiguo al de hoy.
    public static function dias()
    {
        $dias = [];
        for ($i = 29; $i >= 0; $i--) {
            $dias[] = date('Y-m-d', strtotime("-$i day"));
        }
        return $dias;
    }
}
