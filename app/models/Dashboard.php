<?php
// Modelo: Dashboard.php
// Indicadores del panel principal.

class Dashboard
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function ventasSemana(): float
    {
        $stmt = $this->db->query(
            "SELECT COALESCE(SUM(total), 0) AS total FROM pedidos
             WHERE estado_cuenta = 'pagada' AND fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            []
        );
        $row = $stmt->fetch();
        return (float) ($row['total'] ?? 0);
    }

    public function pedidosPorEstado(): array
    {
        $stmt = $this->db->query(
            "SELECT estado, COUNT(*) AS cantidad FROM pedidos GROUP BY estado",
            []
        );
        $resultado = [];
        while ($row = $stmt->fetch()) {
            $resultado[$row['estado']] = (int) $row['cantidad'];
        }
        return $resultado;
    }

    public function reservasHoy(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) AS total FROM reservas WHERE fecha_reserva = CURDATE()",
            []
        );
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function indicadores(): array
    {
        return [
            'ventas_semana'  => $this->ventasSemana(),
            'pedidos_estado' => $this->pedidosPorEstado(),
            'reservas_hoy'   => $this->reservasHoy(),
        ];
    }
}
