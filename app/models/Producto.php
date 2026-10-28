<?php
// Modelo: Producto.php
// Tabla: productos
// Productos del menú con precio y categoría.

class Producto
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            "SELECT p.*, c.nombre AS categoria_nombre FROM productos p
             LEFT JOIN categorias c ON p.categoria_id = c.id
             ORDER BY p.nombre ASC"
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM productos WHERE id = ?", [$id]);
        return $stmt->fetch();
    }

    public function crear(string $nombre, string $descripcion, float $precio, int $categoriaId, bool $disponible = true): bool
    {
        $stmt = $this->db->query(
            "INSERT INTO productos (nombre, descripcion, precio, categoria_id, disponible) VALUES (?, ?, ?, ?, ?)",
            [$nombre, $descripcion, $precio, $categoriaId, $disponible ? 1 : 0]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizar(int $id, string $nombre, string $descripcion, float $precio, int $categoriaId, bool $disponible): bool
    {
        $stmt = $this->db->query(
            "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, categoria_id = ?, disponible = ? WHERE id = ?",
            [$nombre, $descripcion, $precio, $categoriaId, $disponible ? 1 : 0, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query("DELETE FROM productos WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public function obtenerDisponibles(): array
    {
        $stmt = $this->db->query("SELECT * FROM productos WHERE disponible = 1 ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }
}
