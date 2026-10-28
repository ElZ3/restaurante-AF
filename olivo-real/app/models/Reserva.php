<?php
class Reserva {
    public static function estados() {
        return ['pendiente' => 'Pendiente', 'confirmada' => 'Confirmada', 'llego' => 'Llegó',
                'atendida' => 'Atendida', 'cancelada' => 'Cancelada'];
    }

    // BACKEND: SELECT sobre las tablas mesas y areas.
    public static function mesas() {
        return [
          [1,'Mesa 1',2,'Salón principal'], [2,'Mesa 2',4,'Salón principal'], [3,'Mesa 3',4,'Salón principal'],
          [4,'Mesa 4',6,'Salón principal'], [5,'Mesa 5',2,'Salón principal'], [6,'Mesa 6',4,'Salón principal'],
          [7,'Mesa 7',4,'Terraza'],         [8,'Mesa 8',4,'Terraza'],         [9,'Mesa 9',2,'Terraza'],
          [10,'Mesa 10',6,'Terraza'],       [11,'Mesa 11',8,'Área privada'],  [12,'Mesa 12',10,'Área privada'],
        ];
    }

    // BACKEND: SELECT sobre la tabla reservas.
    public static function todas() {
        $hoy    = date('Y-m-d');
        $manana = date('Y-m-d', strtotime('+1 day'));
        // [id, cliente, contacto, fecha, hora, personas, mesa_id, estado, observaciones]
        return [
          [1, 'María García',  '7123-4567', $hoy,    '12:30', 2, 3,  'confirmada', 'Cumpleaños'],
          [2, 'Carlos Pérez',  '7234-5678', $hoy,    '14:00', 4, 6,  'llego',      ''],
          [3, 'Ana Torres',    '7345-6789', $hoy,    '17:00', 4, 8,  'pendiente',  'Prefiere terraza'],
          [4, 'Luis Martínez', '7456-7890', $hoy,    '19:30', 8, 12, 'confirmada', 'Grupo familiar'],
          [5, 'Sofía Ramírez', '7567-8901', $manana, '13:00', 2, 1,  'pendiente',  ''],
          [6, 'Jorge Mejía',   '7678-9012', $manana, '20:00', 6, 4,  'cancelada',  'Canceló por viaje'],
        ];
    }
}