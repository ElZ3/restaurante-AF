<?php
// Modelo: Orden.php
// Tabla: ordenes
// Órdenes/pedidos del restaurante vinculados a mesas y usuarios.
// Consultas SQL reales vía clase Database con transacciones PDO.

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
            "SELECT o.*, u.nombre AS usuario_nombre, m.numero AS mesa_numero FROM ordenes o
             LEFT JOIN usuarios u ON o.usuario_id = u.id
             LEFT JOIN mesas m ON o.mesa_id = m.id
             ORDER BY o.fecha_hora DESC"
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM ordenes WHERE id = ?", [$id]);
        return $stmt->fetch();
    }

    /**
     * Devuelve una orden con sus detalles (items) para renderizado de vista.
     * Claves devueltas: id, mesa_id, usuario_id, total, estado, fecha_hora,
     * usuario_nombre, mesa_numero, items (array con cantidad, nombre, precio_unitario, subtotal).
     */
    public function obtenerConDetalles(int $id): ?array
    {
        $orden = $this->obtenerPorId($id);
        if ($orden === null || $orden === false) {
            return null;
        }

        $detalleModel = new DetalleOrden($this->db);
        $orden['items'] = $detalleModel->obtenerPorOrden($id);

        return $orden;
    }

    public function crear(int $mesaId, int $usuarioId, float $total, string $estado = 'pendiente'): int
    {
        $this->db->beginTransaction();
        try {
            $this->db->query(
                "INSERT INTO ordenes (mesa_id, usuario_id, total, estado, fecha_hora) VALUES (?, ?, ?, ?, NOW())",
                [$mesaId, $usuarioId, $total, $estado]
            );
            $ordenId = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $ordenId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizarEstado(int $id, string $estado): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->query(
                "UPDATE ordenes SET estado = ? WHERE id = ?",
                [$estado, $id]
            );
            $this->db->commit();
            return $stmt->rowCount() >= 0;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function eliminar(int $id): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->query("DELETE FROM detalles_orden WHERE orden_id = ?", [$id]);
            $stmt = $this->db->query("DELETE FROM ordenes WHERE id = ?", [$id]);
            $this->db->commit();
            return $stmt->rowCount() >= 0;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function obtenerPorMesa(int $mesaId): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM ordenes WHERE mesa_id = ? ORDER BY fecha_hora DESC",
            [$mesaId]
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorEstado(string $estado): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM ordenes WHERE estado = ? ORDER BY fecha_hora DESC",
            [$estado]
        );
        return $stmt->fetchAll();
    }
}
