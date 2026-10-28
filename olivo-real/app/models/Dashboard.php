<?php
/**
 * Datos del Dashboard.
 * BACKEND: todos estos datos saldrán de consultas SQL (ventas = pagos de cuentas pagadas).
 */
class Dashboard
{
    public static function indicadores()
    {
        return [
            'ventas_hoy'     => 1230.45,
            'mesas_ocupadas' => 8,
            'mesas_total'    => 12,
            'reservas_prox'  => 3,
            'pedidos_cocina' => 2,
        ];
    }

    public static function ventasSemana()
    {
        return ['Lun' => 620, 'Mar' => 810, 'Mié' => 540, 'Jue' => 930, 'Vie' => 1480, 'Sáb' => 1720, 'Dom' => 1230];
    }

    // [nombre, cantidad, clase de estado]
    public static function pedidosPorEstado()
    {
        return [
          ['Pendientes',     4, 'pendiente'],
          ['En preparación', 4, 'preparacion'],
          ['Listos',         6, 'lista'],
          ['Entregados',    12, 'entregada'],
        ];
    }

    // [hora, cliente, mesa, estado]
    public static function reservasHoy()
    {
        return [
          ['12:30', 'María García',  'Mesa 3',  'confirmada'],
          ['14:00', 'Carlos Pérez',  'Mesa 6',  'confirmada'],
          ['17:00', 'Ana Torres',    'Mesa 8',  'pendiente'],
          ['19:30', 'Luis Martínez', 'Mesa 12', 'confirmada'],
        ];
    }
}
