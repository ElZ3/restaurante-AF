<?php
// Modelo: Categoria.php
// Tabla: categorias
// Categorías de productos del menú (ej. entradas, platos fuertes, bebidas, postres).

class Categoria
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query("SELECT * FROM categorias ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM categorias WHERE id = ?", [$id]);
        return $stmt->fetch();
    }

    public function crear(string $nombre, string $descripcion = ''): bool
    {
        $stmt = $this->db->query(
            "INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)",
            [$nombre, $descripcion]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizar(int $id, string $nombre, string $descripcion = ''): bool
    {
        $stmt = $this->db->query(
            "UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?",
            [$nombre, $descripcion, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query("DELETE FROM categorias WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }
}
