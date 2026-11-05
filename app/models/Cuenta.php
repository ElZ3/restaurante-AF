<?php
// Modelo: Cuenta.php
// Cuentas = pedidos con estado_cuenta 'abierta'. El cobro inserta en `pagos`
// (pedido_id, usuario_id, monto, metodo, fecha, referencia, token_unico)
// y marca pedidos.estado_cuenta = 'pagada'.

class Cuenta
{
    private const METODOS = ['efectivo', 'tarjeta', 'transferencia', 'otro'];

    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerAbiertas(): array
    {
        // COALESCE: los pedidos creados antes de esta corrección tienen estado_cuenta NULL.
        $stmt = $this->db->query(
            "SELECT p.id, p.mesa_id, p.usuario_id, p.estado, p.estado_cuenta, p.total, p.fecha,
                    u.nombre AS usuario_nombre, m.numero_mesa AS mesa_numero
             FROM pedidos p
             LEFT JOIN usuarios u ON p.usuario_id = u.id
             LEFT JOIN mesas m ON p.mesa_id = m.id
             WHERE COALESCE(p.estado_cuenta, 'abierta') = 'abierta'
               AND p.estado IN ('pendiente', 'en_preparacion', 'lista', 'entregada')
             ORDER BY p.fecha ASC",
            []
        );
        $cuentas = $stmt->fetchAll();
        $detalleStmt = $this->db->getConnection()->prepare(
            'SELECT dp.cantidad, dp.precio_unitario, dp.observaciones, pr.nombre AS producto_nombre
             FROM detalle_pedido dp
             LEFT JOIN productos pr ON dp.producto_id = pr.id
             WHERE dp.pedido_id = ?'
        );
        foreach ($cuentas as &$cuenta) {
            $detalleStmt->execute([(int) $cuenta['id']]);
            $cuenta['items'] = array_map(static function (array $item): array {
                return [
                    'cantidad' => (int) $item['cantidad'],
                    'precio' => (float) $item['precio_unitario'],
                    'nombre' => $item['producto_nombre'] ?? 'Producto',
                    'observaciones' => $item['observaciones'] ?? '',
                ];
            }, $detalleStmt->fetchAll());
            $cuenta['mesa'] = $cuenta['mesa_numero'];
        }
        unset($cuenta);

        return $cuentas;
    }

    public function cerrar(int $pedidoId, int $usuarioId, float $recibido, string $metodo, string $referencia = ''): array
    {
        if ($pedidoId < 1 || $usuarioId < 1 || !in_array($metodo, self::METODOS, true)) {
            throw new InvalidArgumentException('Los datos del pago no son válidos.');
        }

        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                "SELECT id, mesa_id, total, estado, estado_cuenta FROM pedidos WHERE id = ? FOR UPDATE"
            );
            $stmt->execute([$pedidoId]);
            $pedido = $stmt->fetch();
            if (!$pedido) {
                throw new InvalidArgumentException('La cuenta no existe.');
            }
            $estadoCuenta = $pedido['estado_cuenta'] ?? 'abierta';
            if ($estadoCuenta === 'pagada') {
                throw new InvalidArgumentException('Esta cuenta ya fue pagada.');
            }
            if ($estadoCuenta === 'anulada') {
                throw new InvalidArgumentException('Esta cuenta está anulada.');
            }
            if (!in_array($pedido['estado'], ['lista', 'entregada'], true)) {
                throw new InvalidArgumentException('El pedido debe estar listo o entregado antes de cobrar.');
            }

            $total = round((float) $pedido['total'], 2);
            if ($total <= 0 || $recibido + 0.00001 < $total) {
                throw new InvalidArgumentException('El monto recibido no cubre el total de la cuenta.');
            }

            $token = bin2hex(random_bytes(16)); // CHAR(32)
            $referencia = trim($referencia);
            $pagoStmt = $pdo->prepare(
                'INSERT INTO pagos (pedido_id, usuario_id, monto, metodo, fecha, referencia, token_unico)
                 VALUES (?, ?, ?, ?, NOW(), ?, ?)'
            );
            $pagoStmt->execute([
                $pedidoId,
                $usuarioId,
                $total,
                $metodo,
                $referencia !== '' ? mb_substr($referencia, 0, 80) : null,
                $token,
            ]);
            $pagoId = (int) $pdo->lastInsertId();

            $pdo->prepare("UPDATE pedidos SET estado_cuenta = 'pagada' WHERE id = ?")->execute([$pedidoId]);
            $pdo->prepare(
                'INSERT INTO historial_pedido (pedido_id, estado, usuario_id, fecha) VALUES (?, ?, ?, NOW())'
            )->execute([$pedidoId, 'pagada', $usuarioId]);

            (new Pedido())->liberarMesaSiNoHayPedidosActivos((int) $pedido['mesa_id'], $pedidoId);

            $pdo->commit();
            return [
                'pago_id' => $pagoId,
                'pedido_id' => $pedidoId,
                'monto' => $total,
                'cambio' => round($recibido - $total, 2),
                'token_unico' => $token,
            ];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
