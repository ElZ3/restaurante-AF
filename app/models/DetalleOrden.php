<?php
// Modelo: DetalleOrden.php
// Tabla: detalles_orden
// Detalle de productos por orden (cantidad, precio unitario, subtotal).
// Consultas SQL reales vía clase Database.

class DetalleOrden
{
    protected $db;

    public function __construct($db = null)
    {
        $this->db = $db instanceof Database ? $db : Database::getInstance();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            "SELECT d.*, o.id AS orden_id, p.nombre AS producto_nombre FROM detalles_orden d
             LEFT JOIN ordenes o ON d.orden_id = o.id
             LEFT JOIN productos p ON d.producto_id = p.id
             ORDER BY d.id DESC"
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM detalles_orden WHERE id = ?", [$id]);
        return $stmt->fetch();
    }

    /**
     * Devuelve los detalles de una orden con las claves exactas que espera la vista:
     * id, orden_id, producto_id, cantidad, precio_unitario, subtotal, nombre (del producto).
     */
    public function obtenerPorOrden(int $ordenId): array
    {
        $stmt = $this->db->query(
            "SELECT d.id, d.orden_id, d.producto_id, d.cantidad, d.precio_unitario, d.subtotal,
                    p.nombre AS nombre
             FROM detalles_orden d
             LEFT JOIN productos p ON d.producto_id = p.id
             WHERE d.orden_id = ?",
            [$ordenId]
        );
        return $stmt->fetchAll();
    }

    public function crear(int $ordenId, int $productoId, int $cantidad, float $precioUnitario, float $subtotal): bool
    {
        $stmt = $this->db->query(
            "INSERT INTO detalles_orden (orden_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)",
            [$ordenId, $productoId, $cantidad, $precioUnitario, $subtotal]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizar(int $id, int $cantidad, float $precioUnitario, float $subtotal): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->query(
                "UPDATE detalles_orden SET cantidad = ?, precio_unitario = ?, subtotal = ? WHERE id = ?",
                [$cantidad, $precioUnitario, $subtotal, $id]
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
        $stmt = $this->db->query("DELETE FROM detalles_orden WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public function eliminarPorOrden(int $ordenId): bool
    {
        $stmt = $this->db->query("DELETE FROM detalles_orden WHERE orden_id = ?", [$ordenId]);
        return $stmt->rowCount() >= 0;
    }
}
