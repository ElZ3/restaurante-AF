<?php
// Modelo: Rol.php
// Tabla: roles
// Mantiene la integridad de los roles de usuario del sistema.

class Rol
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query("SELECT * FROM roles ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM roles WHERE id = ?", [$id]);
        return $stmt->fetch();
    }

    public function crear(string $nombre, string $descripcion = ''): bool
    {
        $stmt = $this->db->query(
            "INSERT INTO roles (nombre, descripcion) VALUES (?, ?)",
            [$nombre, $descripcion]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizar(int $id, string $nombre, string $descripcion = ''): bool
    {
        $stmt = $this->db->query(
            "UPDATE roles SET nombre = ?, descripcion = ? WHERE id = ?",
            [$nombre, $descripcion, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query("DELETE FROM roles WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }
}
