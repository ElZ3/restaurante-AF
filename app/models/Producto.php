<?php
// Modelo: Producto.php
// Tabla: productos (esquema olivo_real.sql)
// Columnas: id, categoria_id, nombre, descripcion, precio, imagen, disponible, creado_en, activo

class Producto
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerTodos(?int $categoriaId = null): array
    {
        $sql = 'SELECT p.id, p.categoria_id, p.nombre, p.descripcion, p.precio,
                    p.imagen, p.disponible, p.creado_en, c.nombre AS categoria_nombre
             FROM productos p
             INNER JOIN categorias c ON p.categoria_id = c.id
             WHERE p.activo = 1 AND c.estado = \'activo\'';
        $params = [];
        if ($categoriaId !== null) {
            $sql .= ' AND p.categoria_id = ?';
            $params[] = $categoriaId;
        }
        $sql .= ' ORDER BY p.nombre ASC';
        return $this->db->query($sql, $params)->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query(
            'SELECT id, categoria_id, nombre, descripcion, precio, imagen, disponible, creado_en, activo
             FROM productos WHERE id = ? AND activo = 1',
            [$id]
        );
        $producto = $stmt->fetch();
        return $producto === false ? null : $producto;
    }

    public function crear(
        string $nombre,
        string $descripcion,
        float $precio,
        int $categoriaId,
        bool $disponible = true,
        string $imagen = ''
    ): bool {
        $stmt = $this->db->query(
            'INSERT INTO productos
                (categoria_id, nombre, descripcion, precio, imagen, disponible, activo, creado_en)
             VALUES (?, ?, ?, ?, ?, ?, 1, NOW())',
            [$categoriaId, $nombre, $descripcion, $precio, $imagen !== '' ? $imagen : null, $disponible ? 1 : 0]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizar(
        int $id,
        string $nombre,
        string $descripcion,
        float $precio,
        int $categoriaId,
        bool $disponible,
        string $imagen = ''
    ): bool {
        if (!$this->obtenerPorId($id)) {
            return false;
        }
        $stmt = $this->db->query(
            'UPDATE productos SET categoria_id = ?, nombre = ?, descripcion = ?, precio = ?, imagen = ?, disponible = ? WHERE id = ? AND activo = 1',
            [$categoriaId, $nombre, $descripcion, $precio, $imagen, $disponible ? 1 : 0, $id]
        );
        return true;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query('UPDATE productos SET activo = 0 WHERE id = ? AND activo = 1', [$id]);
        return $stmt->rowCount() > 0;
    }

    public function toggleDisponible(int $id, bool $disponible): bool
    {
        $stmt = $this->db->query(
            'UPDATE productos SET disponible = ? WHERE id = ? AND activo = 1',
            [$disponible ? 1 : 0, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function obtenerDisponibles(): array
    {
        $stmt = $this->db->query(
            'SELECT id, categoria_id, nombre, descripcion, precio, imagen, disponible, creado_en
             FROM productos
             WHERE disponible = 1 AND activo = 1
             ORDER BY nombre ASC',
            []
        );
        return $stmt->fetchAll();
    }
}
