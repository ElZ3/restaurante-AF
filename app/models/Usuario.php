<?php
// Modelo: Usuario.php
// Tabla: usuarios
// Gestión de usuarios del sistema con verificación de contraseña.

class Usuario
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Login y listados: `email` es el identificador; el hash puede venir en `password` o `contrasena`. */
    private function mapearFila(array $fila): array
    {
        $identificador = $fila['email'] ?? $fila['usuario'] ?? '';
        $fila['email'] = $identificador;
        $fila['usuario'] = $identificador;
        $hash = (string) ($fila['password'] ?? $fila['contrasena'] ?? '');
        $fila['password'] = $hash;
        $fila['contrasena'] = $hash;
        $fila['rol'] = $fila['rol_nombre'] ?? $fila['rol'] ?? $fila['rol_id'] ?? '';
        return $fila;
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            "SELECT u.*, r.nombre AS rol_nombre FROM usuarios u
             LEFT JOIN roles r ON u.rol_id = r.id
             ORDER BY u.id DESC"
        );
        return array_map([$this, 'mapearFila'], $stmt->fetchAll());
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query(
            "SELECT u.*, r.nombre AS rol_nombre FROM usuarios u
             LEFT JOIN roles r ON u.rol_id = r.id
             WHERE u.id = ?",
            [$id]
        );
        $fila = $stmt->fetch();
        return $fila === false ? null : $this->mapearFila($fila);
    }

    public function obtenerPorNombreUsuario(string $usuario): ?array
    {
        $stmt = $this->db->query(
            "SELECT u.*, r.nombre AS rol_nombre FROM usuarios u
             LEFT JOIN roles r ON u.rol_id = r.id
             WHERE u.email = ? AND u.activo = 1",
            [$usuario]
        );
        $fila = $stmt->fetch();
        return $fila === false ? null : $this->mapearFila($fila);
    }

    public function crear(string $nombre, string $usuario, string $contrasena, int $rolId, string $estado = 'activo'): bool
    {
        $hash = password_hash($contrasena, PASSWORD_BCRYPT);
        $stmt = $this->db->query(
            "INSERT INTO usuarios (nombre, email, contrasena, rol_id, estado) VALUES (?, ?, ?, ?, ?)",
            [$nombre, $usuario, $hash, $rolId, $estado]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizar(int $id, string $nombre, string $usuario, int $rolId, string $estado): bool
    {
        $stmt = $this->db->query(
            "UPDATE usuarios SET nombre = ?, email = ?, rol_id = ?, estado = ? WHERE id = ?",
            [$nombre, $usuario, $rolId, $estado, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizarContrasena(int $id, string $contrasena): bool
    {
        $hash = password_hash($contrasena, PASSWORD_BCRYPT);
        $stmt = $this->db->query(
            "UPDATE usuarios SET contrasena = ? WHERE id = ?",
            [$hash, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query("DELETE FROM usuarios WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public function verificarContrasena(string $contrasenaPlana, string $hash): bool
    {
        return password_verify($contrasenaPlana, $hash);
    }
}
