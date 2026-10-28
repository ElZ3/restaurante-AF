<?php
/**
 * Pedidos y órdenes de cocina.
 * BACKEND: saldrán de las tablas pedidos, detalle_pedido e historial_pedido.
 */
class Pedido
{
    private static function item($n, $c, $p, $o = '')
    {
        return ['n' => $n, 'c' => $c, 'p' => $p, 'o' => $o];
    }

    public static function estados()
    {
        return ['borrador' => 'Borrador', 'pendiente' => 'Pendiente', 'en_preparacion' => 'En preparación',
                    'lista' => 'Lista', 'entregada' => 'Entregada', 'cancelado' => 'Cancelado'];
    }

    // Más recientes primero.
    public static function todos()
    {
        $pedidos = [
          ['id' => 101, 'mesa' => 2,  'mesero' => 'Carlos Méndez', 'hora' => '12:10', 'estado' => 'en_preparacion',
           'items' => [self::item('Pasta Alfredo', 1, 12.50), self::item('Ensalada César', 1, 6.50, 'Sin crutones'), self::item('Coca Cola', 2, 2.50, 'Sin hielo')]],
          ['id' => 103, 'mesa' => 4,  'mesero' => 'Laura Gómez',   'hora' => '12:25', 'estado' => 'pendiente',
           'items' => [self::item('Filete de res', 2, 18.00, 'Término medio'), self::item('Salmón a la plancha', 1, 14.00), self::item('Ensalada César', 1, 6.50)]],
          ['id' => 104, 'mesa' => 5,  'mesero' => 'Carlos Méndez', 'hora' => '12:20', 'estado' => 'en_preparacion',
           'items' => [self::item('Filete de res', 1, 18.00)]],
          ['id' => 105, 'mesa' => 6,  'mesero' => 'Laura Gómez',   'hora' => '11:50', 'estado' => 'lista',
           'items' => [self::item('Salmón a la plancha', 1, 14.00), self::item('Filete de res', 1, 18.00)]],
          ['id' => 106, 'mesa' => 7,  'mesero' => 'Carlos Méndez', 'hora' => '11:15', 'estado' => 'entregada',
           'items' => [self::item('Filete de res', 1, 18.00), self::item('Pasta Alfredo', 1, 12.50), self::item('Tiramisú', 2, 5.50)]],
          ['id' => 107, 'mesa' => 8,  'mesero' => 'Laura Gómez',   'hora' => '12:40', 'estado' => 'borrador',
           'items' => [self::item('Salmón a la plancha', 1, 14.00), self::item('Ensalada César', 2, 6.50)]],
          ['id' => 108, 'mesa' => 10, 'mesero' => 'Carlos Méndez', 'hora' => '11:30', 'estado' => 'entregada',
           'items' => [self::item('Filete de res', 2, 18.00), self::item('Salmón a la plancha', 1, 14.00), self::item('Ensalada César', 2, 6.50)]],
          ['id' => 109, 'mesa' => 11, 'mesero' => 'Laura Gómez',   'hora' => '12:30', 'estado' => 'pendiente',
           'items' => [self::item('Filete de res', 4, 18.00), self::item('Salmón a la plancha', 2, 14.00), self::item('Limonada natural', 4, 3.00)]],
        ];
        usort($pedidos, function ($a, $b) {
            return $b['id'] <=> $a['id'];
        });
        return $pedidos;
    }

    // BACKEND: pedidos con estado distinto de borrador/cancelado, con sus líneas de detalle_pedido.
    // Cada orden: [id, mesa, hora, minutos transcurridos, estado, [[producto, cantidad, indicaciones], ...]]
    public static function paraCocina()
    {
        return [
          [103, 4,  '12:25', 3,  'pendiente',      [['Filete de res', 2, 'Término medio'], ['Salmón a la plancha', 1, ''], ['Ensalada César', 1, '']]],
          [109, 11, '12:30', 18, 'pendiente',      [['Filete de res', 4, ''], ['Salmón a la plancha', 2, ''], ['Limonada natural', 4, '']]],
          [101, 2,  '12:10', 14, 'en_preparacion', [['Pasta Alfredo', 1, ''], ['Ensalada César', 1, 'Sin crutones'], ['Coca Cola', 2, 'Sin hielo']]],
          [104, 5,  '12:20', 9,  'en_preparacion', [['Filete de res', 1, '']]],
          [105, 6,  '11:50', 22, 'lista',          [['Salmón a la plancha', 1, ''], ['Filete de res', 1, '']]],
          [106, 7,  '11:15', 70, 'entregada',      [['Filete de res', 1, ''], ['Pasta Alfredo', 1, ''], ['Tiramisú', 2, '']]],
          [108, 10, '11:30', 85, 'entregada',      [['Filete de res', 2, ''], ['Salmón a la plancha', 1, ''], ['Ensalada César', 2, '']]],
        ];
    }

    public static function columnasCocina()
    {
        return [
          'pendiente'      => ['Pendiente',      'Orden recién recibida'],
          'en_preparacion' => ['En preparación', 'Se está cocinando'],
          'lista'          => ['Lista',          'Preparación terminada'],
          'entregada'      => ['Entregada',      'Recibida por el cliente'],
        ];
    }
}
