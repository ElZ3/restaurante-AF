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
            'SELECT p.id, p.categoria_id, p.nombre, p.descripcion, p.precio,
                    p.imagen, p.disponible, p.activo, c.nombre AS categoria_nombre
             FROM productos p
             LEFT JOIN categorias c ON p.categoria_id = c.id
             ORDER BY p.nombre ASC'
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query(
            'SELECT id, categoria_id, nombre, descripcion, precio, imagen, disponible, activo
             FROM productos WHERE id = ?',
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
        bool $activo = true,
        ?string $imagen = null
    ): bool {
        $stmt = $this->db->query(
            'INSERT INTO productos
                (categoria_id, nombre, descripcion, precio, imagen, disponible, activo)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$categoriaId, $nombre, $descripcion, $precio, $imagen, $disponible ? 1 : 0, $activo ? 1 : 0]
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
        ?bool $activo = null,
        ?string $imagen = null
    ): bool {
        $campos = 'categoria_id = ?, nombre = ?, descripcion = ?, precio = ?, imagen = ?, disponible = ?';
        $parametros = [$categoriaId, $nombre, $descripcion, $precio, $imagen, $disponible ? 1 : 0];
        if ($activo !== null) {
            $campos .= ', activo = ?';
            $parametros[] = $activo ? 1 : 0;
        }
        $parametros[] = $id;

        $stmt = $this->db->query(
            'UPDATE productos SET ' . $campos . ' WHERE id = ?',
            $parametros
        );
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query('DELETE FROM productos WHERE id = ?', [$id]);
        return $stmt->rowCount() > 0;
    }

    public function obtenerDisponibles(): array
    {
        $stmt = $this->db->query(
            'SELECT id, categoria_id, nombre, descripcion, precio, imagen, disponible, activo
             FROM productos
             WHERE disponible = 1 AND activo = 1
             ORDER BY nombre ASC'
        );
        return $stmt->fetchAll();
    }
}
