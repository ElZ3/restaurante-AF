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

    private function validarEstado(string $estado): void
    {
        if (!in_array($estado, ['Pendiente', 'En Cocina', 'Listo', 'Entregado'], true)) {
            throw new InvalidArgumentException('Estado de detalle de orden no válido.');
        }
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            'SELECT d.id, d.orden_id, d.producto_id, d.cantidad, d.precio_unitario,
                    d.subtotal, d.estado, d.notas, p.nombre AS producto_nombre
             FROM detalles_orden d
             LEFT JOIN productos p ON d.producto_id = p.id
             ORDER BY d.id DESC'
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query(
            'SELECT id, orden_id, producto_id, cantidad, precio_unitario, subtotal, estado, notas
             FROM detalles_orden WHERE id = ?',
            [$id]
        );
        $detalle = $stmt->fetch();
        return $detalle === false ? null : $detalle;
    }

    /**
     * Devuelve los detalles de una orden con las claves que espera la vista.
     */
    public function obtenerPorOrden(int $ordenId): array
    {
        $stmt = $this->db->query(
            'SELECT d.id, d.orden_id, d.producto_id, d.cantidad, d.precio_unitario,
                    d.subtotal, d.estado, d.notas, p.nombre AS nombre
             FROM detalles_orden d
             LEFT JOIN productos p ON d.producto_id = p.id
             WHERE d.orden_id = ?
             ORDER BY d.id ASC',
            [$ordenId]
        );
        return $stmt->fetchAll();
    }

    public function crear(
        int $ordenId,
        int $productoId,
        int $cantidad,
        float $precioUnitario,
        float $subtotal,
        string $estado = 'Pendiente',
        ?string $notas = null
    ): bool {
        $this->validarEstado($estado);
        $stmt = $this->db->query(
            'INSERT INTO detalles_orden
                (orden_id, producto_id, cantidad, precio_unitario, subtotal, estado, notas)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$ordenId, $productoId, $cantidad, $precioUnitario, $subtotal, $estado, $notas]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizar(
        int $id,
        int $cantidad,
        float $precioUnitario,
        float $subtotal,
        ?string $estado = null,
        ?string $notas = null
    ): bool {
        if ($estado !== null) {
            $this->validarEstado($estado);
        }

        $campos = ['cantidad = ?', 'precio_unitario = ?', 'subtotal = ?'];
        $parametros = [$cantidad, $precioUnitario, $subtotal];
        if ($estado !== null) {
            $campos[] = 'estado = ?';
            $parametros[] = $estado;
        }
        if ($notas !== null) {
            $campos[] = 'notas = ?';
            $parametros[] = $notas;
        }
        $parametros[] = $id;

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->query(
                'UPDATE detalles_orden SET ' . implode(', ', $campos) . ' WHERE id = ?',
                $parametros
            );
            $this->db->commit();
            return $stmt->rowCount() >= 0;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizarEstado(int $id, string $estado, ?string $notas = null): bool
    {
        $this->validarEstado($estado);
        if ($notas === null) {
            $stmt = $this->db->query(
                'UPDATE detalles_orden SET estado = ? WHERE id = ?',
                [$estado, $id]
            );
        } else {
            $stmt = $this->db->query(
                'UPDATE detalles_orden SET estado = ?, notas = ? WHERE id = ?',
                [$estado, $notas, $id]
            );
        }
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query('DELETE FROM detalles_orden WHERE id = ?', [$id]);
        return $stmt->rowCount() > 0;
    }

    public function eliminarPorOrden(int $ordenId): bool
    {
        $stmt = $this->db->query('DELETE FROM detalles_orden WHERE orden_id = ?', [$ordenId]);
        return $stmt->rowCount() >= 0;
    }
}
