<?php
// Modelo: Reserva.php
// Tabla: reservas (DATABASE olivo_real.txt)
// Columnas: id, nombre_cliente, telefono_cliente, fecha_reserva (DATE), hora (TIME), personas,
//           mesa_id, estado ENUM('pendiente','confirmada','llego','atendida','cancelada'),
//           observaciones, creado_por, creado_en
//
// El frontend usa el estado 'completada'; en la base se guarda como 'atendida'.
// La traducción se hace solo aquí (entrada y salida), sin tocar las vistas.

class Reserva
{
    /** Estados que bloquean una mesa en un horario. */
    private const ESTADOS_ACTIVOS = ['pendiente', 'confirmada', 'llego'];

    /** Estados válidos en la base de datos (ENUM real). */
    private const ESTADOS_BD = ['pendiente', 'confirmada', 'llego', 'atendida', 'cancelada'];

    /** Duración por defecto de una reserva (minutos) para calcular choques de horario. */
    private const DURACION_DEFECTO = 120;

    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Convierte el estado que envía el frontend al valor del ENUM de la base. */
    public static function estadoABd(string $estado): string
    {
        $estado = strtolower(trim($estado));
        return $estado === 'completada' ? 'atendida' : $estado;
    }

    /** Convierte el estado guardado en la base al valor que espera el frontend. */
    public static function estadoAVista(string $estado): string
    {
        return $estado === 'atendida' ? 'completada' : $estado;
    }

    /**
     * La fecha_reserva y la hora se exponen en las unidades definidas por el esquema.
     */
    private function selectReservas(): string
    {
        return "SELECT r.id, r.mesa_id, r.nombre_cliente, r.telefono_cliente,
                       r.fecha_reserva,
                       TIME_FORMAT(r.hora, '%H:%i') AS hora,
                       r.personas,
                       CASE r.estado WHEN 'atendida' THEN 'completada' ELSE r.estado END AS estado,
                       r.observaciones, r.creado_por, r.creado_en,
                       m.numero_mesa AS mesa_numero
                FROM reservas r
                LEFT JOIN mesas m ON r.mesa_id = m.id";
    }

    private function validarEstado(string $estadoBd): void
    {
        if (!in_array($estadoBd, self::ESTADOS_BD, true)) {
            throw new InvalidArgumentException('Estado de reserva no válido.');
        }
    }

    private function duracionMinutos(): int
    {
        try {
            $valor = (int) ((new Configuracion())->obtenerPorClave('duracion_reserva_minutos') ?? 0);
        } catch (\Throwable $e) {
            $valor = 0;
        }
        return $valor > 0 ? $valor : self::DURACION_DEFECTO;
    }

    /**
     * Mesas activas con capacidad suficiente y sin otra reserva activa que se cruce
     * en la misma fecha dentro de la ventana de duración de la reserva.
     */
    public function obtenerMesasDisponibles(string $fecha, string $hora, int $personas, int $excluirReservaId = 0): array
    {
        $ventanaSeg = $this->duracionMinutos() * 60;
        $stmt = $this->db->query(
            "SELECT m.id, m.numero_mesa, m.capacidad, m.area_id, a.nombre AS area_nombre
             FROM mesas m
             LEFT JOIN areas a ON a.id = m.area_id
             WHERE m.activo = 1
               AND m.capacidad >= ?
               AND m.estado <> 'fuera_servicio'
               AND NOT EXISTS (
                   SELECT 1 FROM reservas r
                   WHERE r.mesa_id = m.id
                     AND r.fecha_reserva = ?
                     AND r.estado IN ('pendiente', 'confirmada', 'llego')
                     AND r.id <> ?
                     AND ABS(TIME_TO_SEC(TIMEDIFF(r.hora, ?))) < ?
               )
             ORDER BY m.numero_mesa ASC",
            [$personas, $fecha, $excluirReservaId, $hora, $ventanaSeg]
        );
        return $stmt->fetchAll();
    }

    /** Debe ejecutarse dentro de una transacción (usa FOR UPDATE). */
    private function validarMesaDisponible(int $mesaId, string $fecha, string $hora, int $personas, int $excluirReservaId = 0): void
    {
        if ($mesaId < 1) {
            throw new InvalidArgumentException('Selecciona una mesa disponible para la reserva.');
        }
        $stmt = $this->db->query(
            "SELECT id FROM mesas
             WHERE id = ? AND activo = 1 AND capacidad >= ? AND estado <> 'fuera_servicio'
             FOR UPDATE",
            [$mesaId, $personas]
        );
        if (!$stmt->fetch()) {
            throw new InvalidArgumentException('La mesa seleccionada no tiene capacidad o no está disponible.');
        }
        $conflicto = $this->db->query(
            "SELECT id FROM reservas
             WHERE mesa_id = ? AND fecha_reserva = ?
               AND estado IN ('pendiente', 'confirmada', 'llego')
               AND id <> ?
               AND ABS(TIME_TO_SEC(TIMEDIFF(hora, ?))) < ?
             LIMIT 1 FOR UPDATE",
            [$mesaId, $fecha, $excluirReservaId, $hora, $this->duracionMinutos() * 60]
        );
        if ($conflicto->fetch()) {
            throw new InvalidArgumentException('La mesa ya está reservada en ese horario.');
        }
    }

    public function obtenerTodos(string $busqueda = '', string $fecha = '', string $estado = '', int $personasMax = 0): array
    {
        $sql = $this->selectReservas();
        $condiciones = [];
        $params = [];

        if ($estado !== '') {
            $condiciones[] = 'r.estado = ?';
            $params[] = self::estadoABd($estado);
        }
        if ($fecha !== '') {
            $condiciones[] = 'r.fecha_reserva = ?';
            $params[] = $fecha;
        }
        if ($busqueda !== '') {
            $condiciones[] = '(LOWER(r.nombre_cliente) LIKE ? OR LOWER(r.telefono_cliente) LIKE ?)';
            $like = '%' . strtolower($busqueda) . '%';
            $params[] = $like;
            $params[] = $like;
        }
        if ($personasMax > 0) {
            $condiciones[] = 'r.personas <= ?';
            $params[] = $personasMax;
        }

        if ($condiciones) {
            $sql .= ' WHERE ' . implode(' AND ', $condiciones);
        }
        $sql .= ' ORDER BY r.fecha_reserva ASC, r.hora ASC';

        return $this->db->query($sql, $params)->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $reserva = $this->db->query($this->selectReservas() . ' WHERE r.id = ?', [$id])->fetch();
        return $reserva === false ? null : $reserva;
    }

    /**
     * @param string $fecha  Y-m-d (columna DATE fecha_reserva)
     * @param string $hora   H:i o H:i:s (columna TIME hora)
     */
    public function crear(
        ?int $mesaId,
        string $nombreCliente,
        string $fecha,
        string $hora,
        int $personas,
        string $estado = 'confirmada',
        ?string $telefono = null,
        ?int $creadoPor = null,
        ?string $observaciones = null
    ): int {
        $estadoBd = self::estadoABd($estado);
        $this->validarEstado($estadoBd);
        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();
        try {
            if ($mesaId !== null && in_array($estadoBd, self::ESTADOS_ACTIVOS, true)) {
                $this->validarMesaDisponible($mesaId, $fecha, $hora, $personas);
            }
            $stmt = $pdo->prepare(
                'INSERT INTO reservas
                    (nombre_cliente, telefono_cliente, fecha_reserva, hora, personas, mesa_id,
                     estado, observaciones, creado_por, creado_en)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            );
            $stmt->execute([
                $nombreCliente,
                $telefono,
                $fecha,
                $hora,
                $personas,
                $mesaId !== null && $mesaId > 0 ? $mesaId : null,
                $estadoBd,
                $observaciones,
                $creadoPor !== null && $creadoPor > 0 ? $creadoPor : null,
            ]);
            $id = (int) $pdo->lastInsertId();
            $pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public function actualizar(
        int $id,
        ?int $mesaId,
        string $nombreCliente,
        string $fecha,
        string $hora,
        int $personas,
        string $estado,
        ?string $telefono = null,
        ?string $observaciones = null
    ): bool {
        $estadoBd = self::estadoABd($estado);
        $this->validarEstado($estadoBd);
        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();
        try {
            $exists = $pdo->prepare('SELECT id FROM reservas WHERE id = ? FOR UPDATE');
            $exists->execute([$id]);
            if (!$exists->fetch()) {
                $pdo->commit();
                return false;
            }
            if ($mesaId !== null && in_array($estadoBd, self::ESTADOS_ACTIVOS, true)) {
                $this->validarMesaDisponible($mesaId, $fecha, $hora, $personas, $id);
            }
            $stmt = $pdo->prepare(
                'UPDATE reservas
                 SET mesa_id = ?, nombre_cliente = ?, telefono_cliente = ?, fecha_reserva = ?, hora = ?,
                     personas = ?, estado = ?, observaciones = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $mesaId !== null && $mesaId > 0 ? $mesaId : null,
                $nombreCliente,
                $telefono,
                $fecha,
                $hora,
                $personas,
                $estadoBd,
                $observaciones,
                $id,
            ]);
            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public function actualizarEstado(int $id, string $estado): bool
    {
        $estadoBd = self::estadoABd($estado);
        $this->validarEstado($estadoBd);
        if (!$this->obtenerPorId($id)) {
            return false;
        }
        $this->db->query('UPDATE reservas SET estado = ? WHERE id = ?', [$estadoBd, $id]);
        return true;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->query('DELETE FROM reservas WHERE id = ?', [$id]);
        return $stmt->rowCount() > 0;
    }

    public function obtenerPorFecha(string $fecha): array
    {
        return $this->db->query(
            $this->selectReservas() . ' WHERE r.fecha_reserva = ? ORDER BY r.hora ASC',
            [$fecha]
        )->fetchAll();
    }
}
