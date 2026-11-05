<?php
// Controlador: PagoController.php
// Registro de pagos (tabla: pagos). El cobro real lo hace Cuenta::cerrar(), que inserta
// el pago con token_unico y marca el pedido con estado_cuenta = 'pagada' en una sola transacción.

require_once __DIR__ . '/../models/Pago.php';
require_once __DIR__ . '/../models/Cuenta.php';

class PagoController
{
    public function registrar()
    {
        Auth::exigirRol(Menu::roles('cuentas'));
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respuestaJson(['success' => false, 'message' => 'Método no permitido.'], 405);
        }
        if (!Csrf::validar((string) ($_POST['csrf'] ?? ''))) {
            respuestaJson(['success' => false, 'message' => 'Token CSRF inválido.'], 403);
        }

        $pedidoId = (int) ($_POST['pedido_id'] ?? 0);
        $monto = decimalPositivo($_POST['monto'] ?? ($_POST['recibido'] ?? ''));
        $metodo = (string) ($_POST['metodo'] ?? 'efectivo');
        $referencia = (string) ($_POST['referencia'] ?? '');
        if ($monto === null) {
            respuestaJson(['success' => false, 'message' => 'Monto inválido: debe ser mayor a cero.'], 400);
        }

        try {
            $resultado = (new Cuenta())->cerrar(
                $pedidoId,
                (int) (Auth::usuario()['id'] ?? 0),
                (float) $monto,
                $metodo,
                $referencia
            );
            respuestaJson(['success' => true, 'message' => 'Pago registrado correctamente.', 'pago_id' => $resultado['pago_id'], 'pago' => $resultado]);
        } catch (InvalidArgumentException $e) {
            respuestaJson(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\PDOException $e) {
            error_log('[Pagos] registrar: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo registrar el pago (error de base de datos).'], 500);
        } catch (\Throwable $e) {
            error_log('[Pagos] registrar: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo registrar el pago.'], 500);
        }
    }

    public function obtenerPorPedido($id)
    {
        Auth::exigirRol(Menu::roles('cuentas'));
        try {
            $pagoData = (new Pago())->obtenerPorPedido((int) $id);
            respuestaJson(['success' => true, 'pago' => $pagoData]);
        } catch (\PDOException $e) {
            error_log('[Pagos] obtenerPorPedido: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo consultar el pago.'], 500);
        }
    }
}
