<?php
// Modelo: Categoria.php
// Tabla: categorias (esquema olivo_real.sql)
// Columnas: id, nombre, descripcion, estado

class Categoria
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query("SELECT * FROM categorias ORDER BY nombre ASC", []);
        return $stmt->fetchAll();
    }

    public function obtenerActivas(): array
    {
        return $this->db->query(
            "SELECT id, nombre FROM categorias WHERE estado = 'activo' ORDER BY nombre ASC",
            []
        )->fetchAll();
    }

    public function obtenerActivaPorId(int $id): ?array
    {
        $resultado = $this->db->query(
            "SELECT id, nombre FROM categorias WHERE id = ? AND estado = 'activo'",
            [$id]
        )->fetch();
        return $resultado !== false ? $resultado : null;
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM categorias WHERE id = ?", [$id]);
        $resultado = $stmt->fetch();
        return $resultado !== false ? $resultado : null;
    }

    public function crear(string $nombre, string $descripcion = '', string $estado = 'activo'): bool
    {
        $stmt = $this->db->query(
            "INSERT INTO categorias (nombre, descripcion, estado) VALUES (?, ?, ?)",
            [$nombre, $descripcion, $estado]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizar(int $id, string $nombre, string $descripcion = '', string $estado = 'activo'): bool
    {
        $stmt = $this->db->query(
            "UPDATE categorias SET nombre = ?, descripcion = ?, estado = ? WHERE id = ?",
            [$nombre, $descripcion, $estado, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query("DELETE FROM categorias WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }
}
