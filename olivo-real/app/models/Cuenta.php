<?php
/**
 * Cuentas abiertas y pagos del día.
 * BACKEND: saldrán de pedidos (estado_cuenta) y de la tabla pagos.
 */
class Cuenta
{
    private static function item($n, $c, $p)
    {
        return ['n' => $n, 'c' => $c, 'p' => $p];
    }

    // BACKEND: cuentas abiertas = pedidos con estado_cuenta 'abierta' y estado distinto de borrador/cancelado.
    public static function abiertas()
    {
        return [
          ['id' => 106, 'mesa' => 7,  'mesero' => 'Carlos Méndez', 'hora' => '11:15', 'estado' => 'entregada',
           'items' => [self::item('Filete de res', 1, 18.00), self::item('Pasta Alfredo', 1, 12.50), self::item('Tiramisú', 2, 5.50)]],
          ['id' => 108, 'mesa' => 10, 'mesero' => 'Carlos Méndez', 'hora' => '11:30', 'estado' => 'entregada',
           'items' => [self::item('Filete de res', 2, 18.00), self::item('Salmón a la plancha', 1, 14.00), self::item('Ensalada César', 2, 6.50)]],
          ['id' => 105, 'mesa' => 6,  'mesero' => 'Laura Gómez',   'hora' => '11:50', 'estado' => 'lista',
           'items' => [self::item('Salmón a la plancha', 1, 14.00), self::item('Filete de res', 1, 18.00)]],
          ['id' => 101, 'mesa' => 2,  'mesero' => 'Carlos Méndez', 'hora' => '12:10', 'estado' => 'en_preparacion',
           'items' => [self::item('Pasta Alfredo', 1, 12.50), self::item('Ensalada César', 1, 6.50), self::item('Coca Cola', 2, 2.50)]],
        ];
    }

    // BACKEND: pagos del día (tabla pagos). Cada uno: [id, pedido, mesa, método, monto, hora, cajero]
    public static function historial()
    {
        $historial = [
          ['id' => 3, 'pedido' => 98,  'mesa' => 1, 'metodo' => 'efectivo',      'monto' => 24.00, 'hora' => '10:40', 'cajero' => 'Valeria Torres'],
          ['id' => 2, 'pedido' => 99,  'mesa' => 3, 'metodo' => 'tarjeta',       'monto' => 38.50, 'hora' => '11:05', 'cajero' => 'Valeria Torres'],
          ['id' => 1, 'pedido' => 100, 'mesa' => 8, 'metodo' => 'transferencia', 'monto' => 52.00, 'hora' => '11:20', 'cajero' => 'Valeria Torres'],
        ];
        usort($historial, function ($a, $b) {
            return $b['id'] <=> $a['id'];
        });
        return $historial;
    }
}
