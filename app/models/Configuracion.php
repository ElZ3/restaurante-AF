<?php
// Modelo: Configuracion.php
// Tabla: configuracion (clave, valor)

class Configuracion
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query("SELECT clave, valor FROM configuracion", []);
        return $stmt->fetchAll();
    }

    public function obtenerPorClave(string $clave): ?string
    {
        $stmt = $this->db->query("SELECT valor FROM configuracion WHERE clave = ? LIMIT 1", [$clave]);
        $row = $stmt->fetch();
        return $row ? $row['valor'] : null;
    }

    public function actualizar(string $clave, string $valor): bool
    {
        $stmt = $this->db->query(
            "INSERT INTO configuracion (clave, valor) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)",
            [$clave, $valor]
        );
        return $stmt->rowCount() > 0;
    }
}
