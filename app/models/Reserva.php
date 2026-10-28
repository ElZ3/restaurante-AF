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

    private function selectReservas(): string
    {
        return "SELECT r.id, r.mesa_id, r.nombre_cliente AS cliente_nombre,
                       r.telefono_cliente AS cliente_telefono,
                       DATE(r.fecha_reserva) AS fecha_reserva_solo,
                       TIME_FORMAT(r.fecha_reserva, '%H:%i') AS hora_reserva,
                       r.personas, r.estado, m.numero_mesa AS mesa_numero
                FROM reservas r
                LEFT JOIN mesas m ON r.mesa_id = m.id";
    }

    private function validarEstado(string $estado): void
    {
        if (!in_array($estado, ['Confirmada', 'Completada', 'Cancelada'], true)) {
            throw new InvalidArgumentException('Estado de reserva no válido.');
        }
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query($this->selectReservas() . ' ORDER BY r.fecha_reserva ASC');
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query(
            $this->selectReservas() . ' WHERE r.id = ?',
            [$id]
        );
        $reserva = $stmt->fetch();
        return $reserva === false ? null : $reserva;
    }

    public function crear(
        int $mesaId,
        string $clienteNombre,
        string $fechaReserva,
        string $horaReserva,
        int $personas,
        string $estado = 'Confirmada',
        ?string $clienteTelefono = null
    ): bool {
        $this->validarEstado($estado);
        $fechaHora = trim($fechaReserva . ' ' . $horaReserva);
        $stmt = $this->db->query(
            'INSERT INTO reservas
                (mesa_id, nombre_cliente, telefono_cliente, fecha_reserva, personas, estado)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$mesaId, $clienteNombre, $clienteTelefono, $fechaHora, $personas, $estado]
        );
        return $stmt->rowCount() > 0;
    }

    public function actualizarEstado(int $id, string $estado): bool
    {
        $this->validarEstado($estado);
        $stmt = $this->db->query(
            'UPDATE reservas SET estado = ? WHERE id = ?',
            [$estado, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query('DELETE FROM reservas WHERE id = ?', [$id]);
        return $stmt->rowCount() > 0;
    }

    public function obtenerPorFecha(string $fecha): array
    {
        $stmt = $this->db->query(
            $this->selectReservas() . ' WHERE DATE(r.fecha_reserva) = ? ORDER BY r.fecha_reserva ASC',
            [$fecha]
        );
        return $stmt->fetchAll();
    }
}
