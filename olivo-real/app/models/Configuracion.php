<?php
/**
 * Datos de configuración del restaurante: áreas y mesas.
 * BACKEND: saldrán de las tablas configuracion, areas y mesas.
 */
class Configuracion
{
    // [nombre, activa]
    public static function areas()
    {
        return [['Salón principal', 1], ['Terraza', 1], ['Área privada', 1]];
    }

    // [numero, indice del área, capacidad, fuera de servicio]
    public static function mesas()
    {
        return [[1,0,2,0],[2,0,4,0],[3,0,4,0],[4,0,6,0],[5,0,2,0],[6,0,4,0],              // [numero, área, capacidad, fuera de servicio]
                  [7,1,4,0],[8,1,4,0],[9,1,2,1],[10,1,6,0],[11,2,8,0],[12,2,10,0]];
    }
}
