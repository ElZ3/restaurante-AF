<?php
// Modelo: Rol.php
// Tabla: usuarios (columna rol - esquema olivo_real.sql)
// Los roles se gestionan como valores ENUM dentro de la tabla usuarios.

class Rol
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT rol AS nombre FROM usuarios ORDER BY rol ASC",
            []
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query(
            "SELECT id, rol AS nombre FROM usuarios WHERE id = ? LIMIT 1",
            [$id]
        );
        $resultado = $stmt->fetch();
        return $resultado !== false ? $resultado : null;
    }

    public function contarPorRol(string $rol): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) AS total FROM usuarios WHERE rol = ? AND estado = 'activo'",
            [$rol]
        );
        $resultado = $stmt->fetch();
        return (int) ($resultado['total'] ?? 0);
    }

    public function crear(string $nombre, string $descripcion = ''): bool
    {
        // Los roles son ENUM en la tabla usuarios; no se crean registros independientes.
        return true;
    }

    public function actualizar(int $id, string $nombre, string $descripcion = ''): bool
    {
        return true;
    }

    public function eliminar(int $id): bool
    {
        return true;
    }
}
