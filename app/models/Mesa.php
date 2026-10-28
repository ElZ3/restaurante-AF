<?php
// Modelo: Mesa.php
// Tabla: mesas
// Gestión de mesas del restaurante y su estado.

class Mesa
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function validarEstado(string $estado): void
    {
        if (!in_array($estado, ['Libre', 'Ocupada', 'Reservada', 'Mantenimiento'], true)) {
            throw new InvalidArgumentException('Estado de mesa no válido.');
        }
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            'SELECT m.id, m.area_id, m.numero_mesa, m.capacidad, m.estado, m.activo,
                    a.nombre AS area_nombre
             FROM mesas m
             LEFT JOIN areas a ON m.area_id = a.id
             ORDER BY m.id ASC'
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query(
            'SELECT id, area_id, numero_mesa, capacidad, estado, activo
             FROM mesas WHERE id = ?',
            [$id]
        );
        $mesa = $stmt->fetch();
        return $mesa === false ? null : $mesa;
    }

    public function obtenerPorNumero(string $numero): ?array
    {
        $stmt = $this->db->query(
            'SELECT id, area_id, numero_mesa, capacidad, estado, activo
             FROM mesas WHERE numero_mesa = ?',
            [$numero]
        );
        $mesa = $stmt->fetch();
        return $mesa === false ? null : $mesa;
    }

    public function crear(
        string $numero,
        int $capacidad,
        int $areaId,
        string $estado = 'Libre',
        bool $activo = true
    ): bool {
        $this->validarEstado($estado);
        $stmt = $this->db->query(
            'INSERT INTO mesas (numero_mesa, capacidad, area_id, estado, activo)
             VALUES (?, ?, ?, ?, ?)',
            [$numero, $capacidad, $areaId, $estado, $activo ? 1 : 0]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizar(int $id, string $numero, int $capacidad, int $areaId, string $estado): bool
    {
        $this->validarEstado($estado);
        $stmt = $this->db->query(
            'UPDATE mesas SET numero_mesa = ?, capacidad = ?, area_id = ?, estado = ? WHERE id = ?',
            [$numero, $capacidad, $areaId, $estado, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizarEstado(int $id, string $estado): bool
    {
        $this->validarEstado($estado);
        $stmt = $this->db->query(
            'UPDATE mesas SET estado = ? WHERE id = ?',
            [$estado, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizarActivo(int $id, bool $activo): bool
    {
        $stmt = $this->db->query(
            'UPDATE mesas SET activo = ? WHERE id = ?',
            [$activo ? 1 : 0, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query('DELETE FROM mesas WHERE id = ?', [$id]);
        return $stmt->rowCount() > 0;
    }

    public function obtenerDisponibles(): array
    {
        $stmt = $this->db->query(
            "SELECT id, area_id, numero_mesa, capacidad, estado, activo
             FROM mesas
             WHERE estado = 'Libre' AND activo = 1
             ORDER BY numero_mesa ASC"
        );
        return $stmt->fetchAll();
    }
}
