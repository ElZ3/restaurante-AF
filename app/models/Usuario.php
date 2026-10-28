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

    private function selectUsuarios(): string
    {
        return 'SELECT u.id, u.rol_id, u.nombre, u.email, u.password, u.rol,
                       u.activo, u.estado, u.creado_por, u.fecha_creacion,
                       r.nombre AS rol_nombre
                FROM usuarios u
                LEFT JOIN roles r ON u.rol_id = r.id';
    }

    private function mapearFila(array $fila): array
    {
        $identificador = $fila['email'] ?? '';
        $fila['usuario'] = $identificador;
        $fila['contrasena'] = (string) ($fila['password'] ?? '');
        $fila['rol'] = $fila['rol_nombre'] ?? $fila['rol'] ?? $fila['rol_id'] ?? '';
        return $fila;
    }

    private function estadoActivo(string $estado): bool
    {
        return !in_array(strtolower(trim($estado)), ['inactivo', '0', 'false'], true);
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query($this->selectUsuarios() . ' ORDER BY u.id DESC');
        return array_map([$this, 'mapearFila'], $stmt->fetchAll());
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query($this->selectUsuarios() . ' WHERE u.id = ?', [$id]);
        $fila = $stmt->fetch();
        return $fila === false ? null : $this->mapearFila($fila);
    }

    public function obtenerPorNombreUsuario(string $usuario): ?array
    {
        $stmt = $this->db->query(
            $this->selectUsuarios() . ' WHERE u.email = ? AND u.activo = 1',
            [$usuario]
        );
        $fila = $stmt->fetch();
        return $fila === false ? null : $this->mapearFila($fila);
    }

    public function crear(
        string $nombre,
        string $usuario,
        string $contrasena,
        int $rolId,
        string $estado = 'activo',
        ?int $creadoPor = null
    ): bool {
        $hash = password_hash($contrasena, PASSWORD_BCRYPT);
        $activo = $this->estadoActivo($estado) ? 1 : 0;
        $stmt = $this->db->query(
            'INSERT INTO usuarios
                (rol_id, nombre, email, password, rol, activo, estado, creado_por, fecha_creacion)
             VALUES (?, ?, ?, ?, (SELECT nombre FROM roles WHERE id = ?), ?, ?, ?, CURRENT_TIMESTAMP)',
            [$rolId, $nombre, $usuario, $hash, $rolId, $activo, $estado, $creadoPor]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizar(int $id, string $nombre, string $usuario, int $rolId, string $estado): bool
    {
        $activo = $this->estadoActivo($estado) ? 1 : 0;
        $stmt = $this->db->query(
            'UPDATE usuarios
             SET nombre = ?, email = ?, rol_id = ?, rol = (SELECT nombre FROM roles WHERE id = ?),
                 activo = ?, estado = ?
             WHERE id = ?',
            [$nombre, $usuario, $rolId, $rolId, $activo, $estado, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizarContrasena(int $id, string $contrasena): bool
    {
        $hash = password_hash($contrasena, PASSWORD_BCRYPT);
        $stmt = $this->db->query(
            'UPDATE usuarios SET password = ? WHERE id = ?',
            [$hash, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query('DELETE FROM usuarios WHERE id = ?', [$id]);
        return $stmt->rowCount() > 0;
    }

    public function verificarContrasena(string $contrasenaPlana, string $hash): bool
    {
        return password_verify($contrasenaPlana, $hash);
    }
}
