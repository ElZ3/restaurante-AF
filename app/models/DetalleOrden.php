<?php
// Modelo: DetalleOrden.php
// Tabla: detalle_pedido (esquema olivo_real.sql)
// Modelo de compatibilidad; usar DetallePedido.php para operaciones nuevas.

class DetalleOrden
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            "SELECT dp.id, dp.pedido_id, dp.producto_id, dp.cantidad, dp.precio_unitario,
                    dp.observaciones, p.nombre AS producto_nombre FROM detalle_pedido dp
             LEFT JOIN productos p ON dp.producto_id = p.id
             ORDER BY dp.id DESC",
            []
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM detalle_pedido WHERE id = ?", [$id]);
        $resultado = $stmt->fetch();
        return $resultado !== false ? $resultado : null;
    }

    public function obtenerPorPedido(int $pedidoId): array
    {
        $stmt = $this->db->query(
            "SELECT dp.id, dp.pedido_id, dp.producto_id, dp.cantidad, dp.precio_unitario,
                    dp.observaciones, p.nombre AS producto_nombre FROM detalle_pedido dp
             LEFT JOIN productos p ON dp.producto_id = p.id
             WHERE dp.pedido_id = ?",
            [$pedidoId]
        );
        return $stmt->fetchAll();
    }

    public function crear(int $pedidoId, int $productoId, int $cantidad, float $precioUnitario, string $observaciones = ''): bool
    {
        if ($cantidad <= 0) {
            return false;
        }
        if ($precioUnitario < 0) {
            return false;
        }
        $stmt = $this->db->query(
            "INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, observaciones) VALUES (?, ?, ?, ?, ?)",
            [$pedidoId, $productoId, $cantidad, $precioUnitario, $observaciones !== "" ? $observaciones : null]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizar(int $id, int $cantidad, float $precioUnitario, string $observaciones = ''): bool
    {
        if ($cantidad <= 0) {
            return false;
        }
        if ($precioUnitario < 0) {
            return false;
        }
        $stmt = $this->db->query(
            "UPDATE detalle_pedido SET cantidad = ?, precio_unitario = ?, observaciones = ? WHERE id = ?",
            [$cantidad, $precioUnitario, $observaciones !== "" ? $observaciones : null, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query("DELETE FROM detalle_pedido WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public function eliminarPorPedido(int $pedidoId): bool
    {
        $stmt = $this->db->query("DELETE FROM detalle_pedido WHERE pedido_id = ?", [$pedidoId]);
        return $stmt->rowCount() >= 0;
    }
}
