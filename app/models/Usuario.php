<?php
// Modelo: Usuario.php
// Tabla: usuarios (esquema olivo_real.sql)
// Columnas: id, nombre, correo, password_hash, rol, estado, creado_en, creado_por
// Gestión de usuarios del sistema con verificación de contraseña BCRYPT.

class Usuario
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            "SELECT id, nombre, correo, rol, estado, creado_en FROM usuarios ORDER BY id DESC",
            []
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query(
            "SELECT id, nombre, correo, rol, estado, creado_en FROM usuarios WHERE id = ?",
            [$id]
        );
        $resultado = $stmt->fetch();
        return $resultado !== false ? $resultado : null;
    }

    public function obtenerPorCorreo(string $correo): ?array
    {
        $stmt = $this->db->query(
            "SELECT id, nombre, correo, password_hash, rol, estado FROM usuarios WHERE correo = ?",
            [$correo]
        );
        $resultado = $stmt->fetch();
        return $resultado !== false ? $resultado : null;
    }

    public function autenticar(string $correo, string $clave): ?array
    {
        $usuario = $this->obtenerPorCorreo($correo);
        if ($usuario === null || $usuario === false) {
            return null;
        }
        if ($usuario['estado'] !== 'activo') {
            return null;
        }
        if (!password_verify($clave, $usuario['password_hash'])) {
            return null;
        }
        return [
            'id'     => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'rol'    => $usuario['rol'],
        ];
    }

    public function crear(string $nombre, string $correo, string $contrasena, string $rol, string $estado = 'activo'): bool
    {
        $hash = password_hash($contrasena, PASSWORD_BCRYPT);
        $stmt = $this->db->query(
            "INSERT INTO usuarios (nombre, correo, password_hash, rol, estado) VALUES (?, ?, ?, ?, ?)",
            [$nombre, $correo, $hash, $rol, $estado]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizar(int $id, string $nombre, string $correo, string $rol, string $estado): bool
    {
        $stmt = $this->db->query(
            "UPDATE usuarios SET nombre = ?, correo = ?, rol = ?, estado = ? WHERE id = ?",
            [$nombre, $correo, $rol, $estado, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizarContrasena(int $id, string $contrasena): bool
    {
        $hash = password_hash($contrasena, PASSWORD_BCRYPT);
        $stmt = $this->db->query(
            "UPDATE usuarios SET password_hash = ? WHERE id = ?",
            [$hash, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query("UPDATE usuarios SET estado = 'inactivo' WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public function verificarContrasena(string $contrasenaPlana, string $hash): bool
    {
        return password_verify($contrasenaPlana, $hash);
    }
}
