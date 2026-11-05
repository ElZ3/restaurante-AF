<?php
// Modelo: Pedido.php
// Tabla: pedidos (DATABASE olivo_real.txt)
// Columnas: id, mesa_id, usuario_id, reserva_id, fecha, estado, estado_cuenta, total
//   estado        ENUM('borrador','pendiente','en_preparacion','lista','entregada','cancelada')
//   estado_cuenta ENUM('abierta','pagada','anulada')
// Detalle: detalle_pedido (id, pedido_id, producto_id, cantidad, precio_unitario, observaciones)
//
// El frontend usa 'cancelado'; en la base el valor es 'cancelada'. Se traduce aquí.

class Pedido
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Traduce el estado que envía el frontend al valor del ENUM de pedidos. */
    public static function estadoABd(string $estado): string
    {
        $estado = strtolower(trim($estado));
        return $estado === 'cancelado' ? 'cancelada' : $estado;
    }

    /** Traduce el estado de la base al valor que usan las vistas. */
    public static function estadoAVista(string $estado): string
    {
        return $estado === 'cancelada' ? 'cancelado' : $estado;
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            "SELECT p.id, p.mesa_id, p.usuario_id, p.reserva_id, p.fecha, p.estado, p.estado_cuenta, p.total,
                    u.nombre AS usuario_nombre, m.numero_mesa AS mesa_numero FROM pedidos p
             LEFT JOIN usuarios u ON p.usuario_id = u.id
             LEFT JOIN mesas m ON p.mesa_id = m.id
             ORDER BY p.fecha DESC",
            []
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->query(
            'SELECT id, mesa_id, usuario_id, reserva_id, fecha, estado, estado_cuenta, total FROM pedidos WHERE id = ?',
            [$id]
        );
        $resultado = $stmt->fetch();
        return $resultado !== false ? $resultado : null;
    }

    public function crear(int $mesaId, int $usuarioId, array $items, string $estado = 'pendiente', ?int $reservaId = null): int
    {
        if (!in_array($estado, ['borrador', 'pendiente'], true) || !$items) {
            throw new InvalidArgumentException('El pedido debe incluir productos y un estado válido.');
        }
        if ($mesaId < 1) {
            throw new InvalidArgumentException('Selecciona una mesa para el pedido.');
        }

        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();
        try {
            $mesaStmt = $pdo->prepare("SELECT id, estado, activo FROM mesas WHERE id = ? FOR UPDATE");
            $mesaStmt->execute([$mesaId]);
            $mesa = $mesaStmt->fetch();
            if (!$mesa || (int) $mesa['activo'] !== 1 || $mesa['estado'] !== 'disponible') {
                throw new InvalidArgumentException('La mesa seleccionada no está disponible.');
            }

            $total = 0.0;
            $lineas = [];
            $productoStmt = $pdo->prepare(
                "SELECT p.id, p.precio, p.disponible
                 FROM productos p
                 INNER JOIN categorias c ON c.id = p.categoria_id
                 WHERE p.id = ? AND p.activo = 1 AND c.estado = 'activo'"
            );
            foreach ($items as $item) {
                if (!is_array($item)) {
                    throw new InvalidArgumentException('El pedido contiene un producto inválido.');
                }
                // Cantidad: entero > 0 (SMALLINT UNSIGNED). No se aceptan decimales ni negativos.
                $cantidad = (int) (enteroPositivo($item['cantidad'] ?? null, 65535) ?? 0);
                $productoId = (int) (enteroPositivo($item['producto_id'] ?? null) ?? 0);
                if ($cantidad < 1 || $productoId < 1) {
                    throw new InvalidArgumentException('El pedido contiene un producto o cantidad inválidos.');
                }

                $productoStmt->execute([$productoId]);
                $producto = $productoStmt->fetch();
                if (!$producto || !(bool) $producto['disponible']) {
                    throw new InvalidArgumentException('Uno de los productos ya no está disponible.');
                }
                $precioUnitario = (float) $producto['precio'];
                $total += $cantidad * $precioUnitario;
                $observaciones = trim((string) ($item['observaciones'] ?? ''));
                $lineas[] = [
                    'producto_id' => $productoId,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'observaciones' => $observaciones === '' ? null : mb_substr($observaciones, 0, 255),
                ];
            }

            $stmt = $pdo->prepare(
                "INSERT INTO pedidos (mesa_id, usuario_id, reserva_id, fecha, estado, estado_cuenta, total)
                 VALUES (?, ?, ?, NOW(), ?, 'abierta', ?)"
            );
            $stmt->execute([$mesaId, $usuarioId, $reservaId, $estado, round($total, 2)]);
            $pedidoId = (int) $pdo->lastInsertId();

            $detalleStmt = $pdo->prepare(
                'INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, observaciones)
                 VALUES (?, ?, ?, ?, ?)'
            );
            foreach ($lineas as $linea) {
                $detalleStmt->execute([
                    $pedidoId,
                    $linea['producto_id'],
                    $linea['cantidad'],
                    $linea['precio_unitario'],
                    $linea['observaciones'],
                ]);
            }

            $this->registrarHistorial($pedidoId, $estado, $usuarioId);

            if ($estado === 'pendiente') {
                $pdo->prepare("UPDATE mesas SET estado = 'ocupada' WHERE id = ?")->execute([$mesaId]);
            }

            $pdo->commit();
            return $pedidoId;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public function actualizarEstado(int $id, string $estado, ?int $usuarioId = null): bool
    {
        $estado = self::estadoABd($estado);
        $transiciones = [
            'borrador' => ['pendiente', 'cancelada'],
            'pendiente' => ['en_preparacion', 'cancelada'],
            'en_preparacion' => ['lista', 'cancelada'],
            'lista' => ['entregada'],
            'entregada' => [],
            'cancelada' => [],
        ];
        if (!array_key_exists($estado, $transiciones)) {
            throw new InvalidArgumentException('Estado de pedido no válido.');
        }

        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT mesa_id, estado, estado_cuenta FROM pedidos WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $pedido = $stmt->fetch();
            if (!$pedido) {
                $pdo->commit();
                return false;
            }
            if (($pedido['estado_cuenta'] ?? 'abierta') === 'pagada') {
                throw new InvalidArgumentException('El pedido ya fue pagado y no puede cambiar de estado.');
            }
            if (!in_array($estado, $transiciones[$pedido['estado']] ?? [], true)) {
                throw new InvalidArgumentException('La transición de estado solicitada no es válida.');
            }

            if ($estado === 'cancelada') {
                $pdo->prepare("UPDATE pedidos SET estado = 'cancelada', estado_cuenta = 'anulada' WHERE id = ?")
                    ->execute([$id]);
            } else {
                $pdo->prepare('UPDATE pedidos SET estado = ? WHERE id = ?')->execute([$estado, $id]);
            }

            $mesaId = (int) $pedido['mesa_id'];
            if ($estado === 'pendiente') {
                $pdo->prepare("UPDATE mesas SET estado = 'ocupada' WHERE id = ?")->execute([$mesaId]);
            } elseif ($estado === 'cancelada') {
                $this->liberarMesaSiNoHayPedidosActivos($mesaId, $id);
            }

            $this->registrarHistorial($id, $estado, $usuarioId);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /** Libera la mesa solo si no quedan otros pedidos activos con cuenta abierta. */
    public function liberarMesaSiNoHayPedidosActivos(int $mesaId, int $excluirPedidoId = 0): void
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) AS c FROM pedidos
             WHERE mesa_id = ? AND id <> ?
               AND estado IN ('pendiente', 'en_preparacion', 'lista', 'entregada')
               AND COALESCE(estado_cuenta, 'abierta') = 'abierta'",
            [$mesaId, $excluirPedidoId]
        );
        if ((int) ($stmt->fetch()['c'] ?? 0) === 0) {
            $this->db->query("UPDATE mesas SET estado = 'disponible' WHERE id = ?", [$mesaId]);
        }
    }

    /** Inserta en historial_pedido (pedido_id, estado, usuario_id, fecha). */
    public function registrarHistorial(int $pedidoId, string $estado, ?int $usuarioId = null): void
    {
        $this->db->query(
            'INSERT INTO historial_pedido (pedido_id, estado, usuario_id, fecha) VALUES (?, ?, ?, NOW())',
            [$pedidoId, mb_substr($estado, 0, 30), $usuarioId !== null && $usuarioId > 0 ? $usuarioId : null]
        );
    }

    public function eliminar(int $id): bool
    {
        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();
        try {
            $pdo->prepare("DELETE FROM historial_pedido WHERE pedido_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM detalle_pedido WHERE pedido_id = ?")->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id = ?");
            $stmt->execute([$id]);
            $ok = $stmt->rowCount() > 0;
            $pdo->commit();
            return $ok;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public function obtenerPorMesa(int $mesaId): array
    {
        return $this->db->query(
            "SELECT id, mesa_id, usuario_id, reserva_id, fecha, estado, estado_cuenta, total
             FROM pedidos WHERE mesa_id = ? ORDER BY fecha DESC",
            [$mesaId]
        )->fetchAll();
    }

    public function obtenerDetalles(int $pedidoId): array
    {
        return $this->db->query(
            "SELECT dp.id, dp.pedido_id, dp.producto_id, dp.cantidad, dp.precio_unitario, dp.observaciones,
                    pr.nombre AS producto_nombre
             FROM detalle_pedido dp
             LEFT JOIN productos pr ON dp.producto_id = pr.id
             WHERE dp.pedido_id = ?",
            [$pedidoId]
        )->fetchAll();
    }
}
