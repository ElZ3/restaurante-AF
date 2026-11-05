<?php
// Modelo: Pago.php
// Tabla: pagos (DATABASE olivo_real.txt)
// Columnas: id, pedido_id, usuario_id, monto, metodo ENUM('efectivo','tarjeta','transferencia','otro'),
//           fecha, referencia, token_unico CHAR(32)

class Pago
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerTodos(): array
    {
        return $this->db->query(
            "SELECT p.*, u.nombre AS usuario_nombre FROM pagos p
             LEFT JOIN usuarios u ON p.usuario_id = u.id
             ORDER BY p.fecha DESC",
            []
        )->fetchAll();
    }

    public function obtenerHoy(): array
    {
        return $this->db->query(
            "SELECT pg.id, pg.pedido_id, pg.metodo, pg.monto, pg.fecha, pg.referencia,
                    u.nombre AS usuario_nombre, m.numero_mesa AS mesa_numero
             FROM pagos pg
             LEFT JOIN usuarios u ON pg.usuario_id = u.id
             LEFT JOIN pedidos p ON pg.pedido_id = p.id
             LEFT JOIN mesas m ON p.mesa_id = m.id
             WHERE pg.fecha >= CURDATE() AND pg.fecha < CURDATE() + INTERVAL 1 DAY
             ORDER BY pg.fecha DESC",
            []
        )->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $resultado = $this->db->query("SELECT * FROM pagos WHERE id = ?", [$id])->fetch();
        return $resultado !== false ? $resultado : null;
    }

    public function obtenerPorPedido(int $pedidoId): ?array
    {
        $resultado = $this->db->query(
            "SELECT * FROM pagos WHERE pedido_id = ? ORDER BY fecha DESC LIMIT 1",
            [$pedidoId]
        )->fetch();
        return $resultado !== false ? $resultado : null;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query("DELETE FROM pagos WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }
}
