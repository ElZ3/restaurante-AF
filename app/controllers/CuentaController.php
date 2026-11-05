<?php

class CuentaController
{
    public function index(): void
    {
        Auth::exigirRol(Menu::roles('cuentas'));
        $cuentas = [];
        $historial = [];
        try {
            $cuentas = (new Cuenta())->obtenerAbiertas();
            $historial = array_map(static function (array $pago): array {
                return [
                    'id' => (int) $pago['id'],
                    'pedido' => (int) $pago['pedido_id'],
                    'mesa' => $pago['mesa_numero'],
                    'metodo' => $pago['metodo'],
                    'monto' => (float) $pago['monto'],
                    'hora' => substr((string) $pago['fecha'], 11, 5),
                    'cajero' => $pago['usuario_nombre'] ?? 'Cajero',
                ];
            }, (new Pago())->obtenerHoy());
        } catch (\PDOException $e) {
            // La página se dibuja vacía en lugar de mostrar un Fatal error.
            error_log('[Cuentas] index: ' . $e->getMessage());
        }
        $usuario = Auth::usuario();
        $cajero = $usuario['nombre'] ?? 'Cajero';
        $usuario_id = (int) ($usuario['id'] ?? 0);
        $descuento = false;

        require __DIR__ . '/../../public/views/cuentas/index.php';
    }

    public function registrarPago(): void
    {
        Auth::exigirRol(Menu::roles('cuentas'));
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respuestaJson(['success' => false, 'message' => 'Método no permitido.'], 405);
        }
        if (!Csrf::validar((string) ($_POST['csrf'] ?? ''))) {
            respuestaJson(['success' => false, 'message' => 'Token CSRF inválido.'], 403);
        }

        $pedidoId = (int) ($_POST['pedido_id'] ?? 0);
        $recibido = decimalPositivo($_POST['recibido'] ?? '');
        $metodo = (string) ($_POST['metodo'] ?? '');
        $referencia = (string) ($_POST['referencia'] ?? '');
        if ($recibido === null) {
            respuestaJson(['success' => false, 'message' => 'El monto recibido debe ser un número mayor a 0 (máximo 2 decimales).'], 400);
        }

        try {
            $resultado = (new Cuenta())->cerrar(
                $pedidoId,
                (int) (Auth::usuario()['id'] ?? 0),
                (float) $recibido,
                $metodo,
                $referencia
            );
            respuestaJson(['success' => true, 'message' => 'Pago registrado y mesa liberada.', 'pago' => $resultado]);
        } catch (InvalidArgumentException $e) {
            respuestaJson(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\PDOException $e) {
            error_log('[Cuentas] registrarPago: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo registrar el pago (error de base de datos).'], 500);
        } catch (\Throwable $e) {
            error_log('[Cuentas] registrarPago: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo registrar el pago.'], 500);
        }
    }
}
