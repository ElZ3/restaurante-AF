<?php
// Controlador: PedidoController.php
// CRUD de pedidos con transacciones (tablas: pedidos, detalle_pedido, historial_pedido).

require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Mesa.php';
require_once __DIR__ . '/../models/Usuario.php';

class PedidoController
{
    private function exigirAcceso(): array
    {
        Auth::exigirRol(Menu::roles('pedidos'));
        return Auth::usuario();
    }

    private function exigirPostConCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respuestaJson(['success' => false, 'message' => 'Método no permitido.'], 405);
        }
        if (!Csrf::validar((string) ($_POST['csrf'] ?? ''))) {
            respuestaJson(['success' => false, 'message' => 'Token CSRF inválido.'], 403);
        }
    }

    public function index()
    {
        $this->exigirAcceso();
        $modelo = new Pedido();
        $pedidos = $modelo->obtenerTodos();
        require_once __DIR__ . '/../../public/views/pedidos/index.php';
    }

    public function crear()
    {
        $usuario = $this->exigirAcceso();
        $this->exigirPostConCsrf();

        $mesaId = (int) ($_POST['mesa_id'] ?? 0);
        $estado = (string) ($_POST['estado'] ?? 'pendiente');
        $reservaId = (int) ($_POST['reserva_id'] ?? 0);
        $items = json_decode((string) ($_POST['items'] ?? '[]'), true);
        if (!is_array($items)) {
            respuestaJson(['success' => false, 'message' => 'La lista de productos no es válida.'], 400);
        }
        if ($mesaId < 1) {
            respuestaJson(['success' => false, 'message' => 'Selecciona una mesa para el pedido.'], 400);
        }

        try {
            $pedidoId = (new Pedido())->crear(
                $mesaId,
                (int) ($usuario['id'] ?? 0),
                $items,
                $estado,
                $reservaId > 0 ? $reservaId : null
            );
            respuestaJson(['success' => true, 'message' => 'Pedido creado correctamente.', 'pedido_id' => $pedidoId]);
        } catch (InvalidArgumentException $e) {
            respuestaJson(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\PDOException $e) {
            error_log('[Pedidos] crear: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo guardar el pedido (error de base de datos).'], 500);
        } catch (\Throwable $e) {
            error_log('[Pedidos] crear: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo guardar el pedido.'], 500);
        }
    }

    public function obtenerPorId($id)
    {
        $this->exigirAcceso();
        try {
            $modelo = new Pedido();
            $pedido = $modelo->obtenerPorId((int) $id);
            if (!$pedido) {
                respuestaJson(['success' => false, 'message' => 'Pedido no encontrado.'], 404);
            }
            $pedido['estado'] = Pedido::estadoAVista((string) $pedido['estado']);
            $detalles = $modelo->obtenerDetalles((int) $id);
            respuestaJson(['success' => true, 'pedido' => $pedido, 'detalles' => $detalles]);
        } catch (\PDOException $e) {
            error_log('[Pedidos] obtener: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo consultar el pedido.'], 500);
        }
    }

    public function actualizarEstado($id)
    {
        $usuario = $this->exigirAcceso();
        $this->exigirPostConCsrf();

        $estado = (string) ($_POST['estado'] ?? 'pendiente');
        try {
            $ok = (new Pedido())->actualizarEstado((int) $id, $estado, (int) ($usuario['id'] ?? 0));
            if (!$ok) {
                respuestaJson(['success' => false, 'message' => 'Pedido no encontrado.'], 404);
            }
            // Se devuelve el mismo valor que envió el frontend (p. ej. 'cancelado').
            respuestaJson(['success' => true, 'message' => 'Estado actualizado.', 'pedido_id' => (int) $id, 'estado' => $estado]);
        } catch (InvalidArgumentException $e) {
            respuestaJson(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\PDOException $e) {
            error_log('[Pedidos] actualizarEstado: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo actualizar el estado del pedido.'], 500);
        }
    }

    public function eliminar($id)
    {
        $this->exigirAcceso();
        $this->exigirPostConCsrf();

        try {
            $ok = (new Pedido())->eliminar((int) $id);
            respuestaJson(['success' => $ok, 'message' => $ok ? 'Pedido eliminado.' : 'Pedido no encontrado.'], $ok ? 200 : 404);
        } catch (\PDOException $e) {
            error_log('[Pedidos] eliminar: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se puede eliminar un pedido con pagos registrados.'], 409);
        }
    }
}
