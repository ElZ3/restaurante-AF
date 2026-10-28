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

    private function selectOrdenes(): string
    {
        return 'SELECT o.id, o.mesa_id, o.usuario_id, o.estado, o.total,
                       o.creado_en AS fecha_hora, o.cerrado_en,
                       u.nombre AS usuario_nombre, m.numero_mesa AS mesa_numero
                FROM ordenes o
                JOIN mesas m ON o.mesa_id = m.id
                LEFT JOIN usuarios u ON o.usuario_id = u.id';
    }

    private function validarEstado(string $estado): void
    {
        if (!in_array($estado, ['Abierta', 'En Preparacion', 'Servida', 'Pagada', 'Cancelada'], true)) {
            throw new InvalidArgumentException('Estado de orden no válido.');
        }
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query($this->selectOrdenes() . ' ORDER BY o.creado_en DESC');
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query(
            $this->selectOrdenes() . ' WHERE o.id = ?',
            [$id]
        );
        $orden = $stmt->fetch();
        return $orden === false ? null : $orden;
    }

    /**
     * Devuelve una orden con sus detalles (items) para renderizado de vista.
     * fecha_hora es un alias de creado_en.
     */
    public function obtenerConDetalles(int $id): ?array
    {
        $orden = $this->obtenerPorId($id);
        if ($orden === null) {
            return null;
        }

        $detalleModel = new DetalleOrden($this->db);
        $orden['items'] = $detalleModel->obtenerPorOrden($id);

        return $orden;
    }

    public function crear(int $mesaId, int $usuarioId, float $total, string $estado = 'Abierta'): int
    {
        $this->validarEstado($estado);
        $this->db->beginTransaction();
        try {
            $this->db->query(
                'INSERT INTO ordenes (mesa_id, usuario_id, estado, total, creado_en)
                 VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)',
                [$mesaId, $usuarioId, $estado, $total]
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
        $this->validarEstado($estado);
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->query(
                "UPDATE ordenes
                 SET estado = ?, cerrado_en = CASE
                     WHEN ? IN ('Pagada', 'Cancelada') THEN CURRENT_TIMESTAMP
                     ELSE NULL
                 END
                 WHERE id = ?",
                [$estado, $estado, $id]
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
            $this->db->query('DELETE FROM detalles_orden WHERE orden_id = ?', [$id]);
            $stmt = $this->db->query('DELETE FROM ordenes WHERE id = ?', [$id]);
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
            $this->selectOrdenes() . ' WHERE o.mesa_id = ? ORDER BY o.creado_en DESC',
            [$mesaId]
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorEstado(string $estado): array
    {
        $this->validarEstado($estado);
        $stmt = $this->db->query(
            $this->selectOrdenes() . ' WHERE o.estado = ? ORDER BY o.creado_en DESC',
            [$estado]
        );
        return $stmt->fetchAll();
    }
}
