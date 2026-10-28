<?php
/**
 * Mesas y su estado.
 * BACKEND: SELECT de areas y mesas. El estado 'ocupada' lo da un pedido activo; 'reservada', una reserva próxima.
 */
class Mesa
{
    // Cada mesa: [numero, capacidad, estado, detalle]
    public static function porAreas()
    {
        return [
          'Salón principal' => [
            [1, 2, 'disponible', ''],
            [2, 4, 'ocupada',    'Pedido #101 · $24.00'],
            [3, 4, 'reservada',  'María García · 12:30'],
            [4, 6, 'ocupada',    'Pedido #103 · $56.50'],
            [5, 2, 'ocupada',    'Pedido #104 · $18.00'],
            [6, 4, 'ocupada',    'Pedido #105 · $32.00'],
          ],
          'Terraza' => [
            [7, 4, 'ocupada',        'Pedido #106 · $41.50'],
            [8, 4, 'ocupada',        'Pedido #107 · $27.00'],
            [9, 2, 'fuera_servicio', 'Mantenimiento'],
            [10, 6, 'ocupada',       'Pedido #108 · $63.00'],
          ],
          'Área privada' => [
            [11, 8, 'ocupada',   'Pedido #109 · $112.00'],
            [12, 10, 'reservada', 'Luis Martínez · 19:30'],
          ],
        ];
    }

    public static function estados()
    {
        return ['disponible' => 'Disponible', 'reservada' => 'Reservada', 'ocupada' => 'Ocupada', 'fuera_servicio' => 'Fuera de servicio'];
    }

    // Acciones por estado: [texto, icono, enlace o '' si solo es demo, mensaje demo]
    public static function acciones()
    {
        return [
          'disponible' => [['Asignar reserva', 'bi-calendar-plus', 'reservas.php', ''],
                           ['Comenzar servicio', 'bi-play-circle', '', 'Servicio iniciado'],
                           ['Marcar fuera de servicio', 'bi-slash-circle', '', 'Mesa fuera de servicio']],
          'reservada'  => [['Ver reserva', 'bi-calendar-check', 'reservas.php', ''],
                           ['Comenzar servicio', 'bi-play-circle', '', 'Servicio iniciado']],
          'ocupada'    => [['Ver pedido activo', 'bi-receipt', 'pedidos.php', ''],
                           ['Ir a la cuenta', 'bi-cash-coin', 'cuentas.php', ''],
                           ['Liberar tras cerrar cuenta', 'bi-unlock', '', 'Mesa liberada']],
          'fuera_servicio' => [['Habilitar mesa', 'bi-check-circle', '', 'Mesa habilitada']],
        ];
    }

    public static function conteoPorEstado()
    {
        $conteo = array_fill_keys(array_keys(self::estados()), 0);
        foreach (self::porAreas() as $lista) {
            foreach ($lista as $m) {
                $conteo[$m[2]]++;
            }
        }
        return $conteo;
    }

    // BACKEND: mesas que no están fuera de servicio. [numero, capacidad]; la mesa 9 está fuera de servicio y no aparece.
    public static function paraPedidos()
    {
        return [[1,2],[2,4],[3,4],[4,6],[5,2],[6,4],[7,4],[8,4],[10,6],[11,8],[12,10]];
    }
}
