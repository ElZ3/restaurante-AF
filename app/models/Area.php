<?php
// Modelo: Area.php
// Tabla: areas
// Áreas o sectores del restaurante (ej. comedor, terraza, barra, VIP).

class Area
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query("SELECT * FROM areas ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM areas WHERE id = ?", [$id]);
        return $stmt->fetch();
    }

    public function crear(string $nombre, string $descripcion = ''): bool
    {
        $stmt = $this->db->query(
            "INSERT INTO areas (nombre, descripcion) VALUES (?, ?)",
            [$nombre, $descripcion]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizar(int $id, string $nombre, string $descripcion = ''): bool
    {
        $stmt = $this->db->query(
            "UPDATE areas SET nombre = ?, descripcion = ? WHERE id = ?",
            [$nombre, $descripcion, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query("DELETE FROM areas WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }
}
