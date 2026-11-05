<?php
// Modelo: Reporte.php
// Reportes y estadísticas del sistema.

class Reporte
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function tipos(): array
    {
        return ['ventas', 'pedidos', 'productos', 'mesas'];
    }

    public function productos(string $tipo = 'todos'): array
    {
        if ($tipo === 'mas_vendidos') {
            $stmt = $this->db->query(
                "SELECT pr.nombre, SUM(dp.cantidad) AS total_vendido FROM detalle_pedido dp
                 LEFT JOIN productos pr ON dp.producto_id = pr.id
                 GROUP BY pr.id ORDER BY total_vendido DESC LIMIT 10",
                []
            );
        } else {
            $stmt = $this->db->query("SELECT * FROM productos ORDER BY nombre ASC", []);
        }
        return $stmt->fetchAll();
    }

    public function dias(int $dias = 7): array
    {
        $stmt = $this->db->query(
            "SELECT DATE(fecha) AS dia, COUNT(*) AS total_pedidos FROM pedidos
             WHERE fecha >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY DATE(fecha) ORDER BY dia",
            [$dias]
        );
        return $stmt->fetchAll();
    }
}
