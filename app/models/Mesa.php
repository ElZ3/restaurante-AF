<?php
// Modelo: Mesa.php
// Tabla: mesas
// Gestión de mesas del restaurante y su estado (disponible/ocupada/reservada).

class Mesa
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            "SELECT m.*, a.nombre AS area_nombre FROM mesas m
             LEFT JOIN areas a ON m.area_id = a.id
             ORDER BY m.id ASC"
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM mesas WHERE id = ?", [$id]);
        return $stmt->fetch();
    }

    public function obtenerPorNumero(string $numero): ?array
    {
        $stmt = $this->db->query("SELECT * FROM mesas WHERE numero = ?", [$numero]);
        return $stmt->fetch();
    }

    public function crear(string $numero, int $capacidad, int $areaId, string $estado = 'disponible'): bool
    {
        $stmt = $this->db->query(
            "INSERT INTO mesas (numero, capacidad, area_id, estado) VALUES (?, ?, ?, ?)",
            [$numero, $capacidad, $areaId, $estado]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizar(int $id, string $numero, int $capacidad, int $areaId, string $estado): bool
    {
        $stmt = $this->db->query(
            "UPDATE mesas SET numero = ?, capacidad = ?, area_id = ?, estado = ? WHERE id = ?",
            [$numero, $capacidad, $areaId, $estado, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizarEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->query(
            "UPDATE mesas SET estado = ? WHERE id = ?",
            [$estado, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query("DELETE FROM mesas WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public function obtenerDisponibles(): array
    {
        $stmt = $this->db->query("SELECT * FROM mesas WHERE estado = 'disponible' ORDER BY numero ASC");
        return $stmt->fetchAll();
    }
}
