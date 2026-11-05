<?php
// Modelo: Orden.php (DEPRECADO)
// Tabla: pedidos (esquema olivo_real.sql)
// Este modelo se mantiene por compatibilidad; usar Pedido.php para operaciones nuevas.

class Orden
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            "SELECT p.id, p.mesa_id, p.usuario_id, p.reserva_id, p.fecha, p.estado, p.estado_cuenta, p.total,
                    u.nombre AS usuario_nombre, m.numero_mesa AS mesa_numero FROM pedidos p
             LEFT JOIN usuarios u ON p.usuario_id = u.id
             LEFT JOIN mesas m ON p.mesa_id = m.id
             ORDER BY p.fecha DESC",
            []
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query(
            'SELECT id, mesa_id, usuario_id, reserva_id, fecha, estado, estado_cuenta, total FROM pedidos WHERE id = ?',
            [$id]
        );
        $resultado = $stmt->fetch();
        return $resultado !== false ? $resultado : null;
    }

    public function actualizarEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->query("UPDATE pedidos SET estado = ? WHERE id = ?", [$estado, $id]);
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();
        try {
            $detalleStmt = $pdo->prepare("DELETE FROM detalle_pedido WHERE pedido_id = ?");
            $detalleStmt->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id = ?");
            $resultado = $stmt->execute([$id]);
            $pdo->commit();
            return (bool) $resultado;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
