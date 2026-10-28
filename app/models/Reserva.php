<?php
// Modelo: Reserva.php
// Tabla: reservas
// Reservas de mesas para clientes.

class Reserva
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            "SELECT r.*, u.nombre AS usuario_nombre, m.numero AS mesa_numero FROM reservas r
             LEFT JOIN usuarios u ON r.usuario_id = u.id
             LEFT JOIN mesas m ON r.mesa_id = m.id
             ORDER BY r.fecha_reserva DESC"
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM reservas WHERE id = ?", [$id]);
        return $stmt->fetch();
    }

    public function crear(int $mesaId, int $usuarioId, string $clienteNombre, string $fechaReserva, string $horaReserva, int $personas, string $estado = 'pendiente'): bool
    {
        $stmt = $this->db->query(
            "INSERT INTO reservas (mesa_id, usuario_id, cliente_nombre, fecha_reserva, hora_reserva, personas, estado) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$mesaId, $usuarioId, $clienteNombre, $fechaReserva, $horaReserva, $personas, $estado]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizarEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->query(
            "UPDATE reservas SET estado = ? WHERE id = ?",
            [$estado, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query("DELETE FROM reservas WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public function obtenerPorFecha(string $fecha): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM reservas WHERE fecha_reserva = ? ORDER BY hora_reserva ASC",
            [$fecha]
        );
        return $stmt->fetchAll();
    }
}
