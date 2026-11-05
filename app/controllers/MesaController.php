<?php
// Controlador: MesaController.php
// Maneja la gestión de mesas del restaurante (CRUD + cambio de estado).

require_once __DIR__ . '/../models/Mesa.php';
require_once __DIR__ . '/../models/Area.php';

class MesaController
{
    // Muestra el listado de mesas.
    public function index()
    {
        Auth::exigirRol(Menu::roles('mesas'));

        $modeloMesa = new Mesa();
        $mesas = $modeloMesa->obtenerTodos();
        $areas = (new Area())->obtenerTodos();

        require_once __DIR__ . '/../../public/views/mesas/index.php';
    }

    // Muestra el formulario para crear una nueva mesa.
    public function nuevo()
    {
        Auth::exigirRol(Menu::roles('mesas'));

        $modeloArea = new Area();
        $areas = $modeloArea->obtenerTodos();

        require_once __DIR__ . '/../../public/views/mesas/index.php';
    }

    // Guarda una nueva mesa.
    public function crear()
    {
        Auth::exigirRol(Menu::roles('mesas'));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respuestaJson(['success' => false, 'message' => 'Método no permitido.'], 405);
        }

        if (!Csrf::validar((string) ($_POST['csrf'] ?? ''))) {
            respuestaJson(['success' => false, 'message' => 'Token CSRF inválido.'], 403);
        }

        $numero   = (int) (enteroPositivo($_POST['numero'] ?? null, 65535) ?? 0);
        $capacidad = (int) (enteroPositivo($_POST['capacidad'] ?? null, 255) ?? 0);
        $areaId   = (int) ($_POST['area_id'] ?? 0);
        $estado   = $_POST['estado'] ?? 'disponible';

        $modeloMesa = new Mesa();
        try {
            if ($numero < 1 || $capacidad < 1 || !(new Area())->obtenerPorId($areaId)) {
                respuestaJson(['success' => false, 'message' => 'Número, capacidad y área son obligatorios.'], 400);
            }
            if ($modeloMesa->obtenerPorNumero($numero)) {
                respuestaJson(['success' => false, 'message' => 'Ya existe una mesa con ese número.'], 409);
            }
            $ok = $modeloMesa->crear($numero, $capacidad, $areaId, $estado);
            respuestaJson(
                ['success' => $ok, 'message' => $ok ? 'Mesa creada correctamente.' : 'No se pudo crear la mesa.'],
                $ok ? 201 : 400,
                $ok ? ['mesa_id' => (int) Database::getInstance()->lastInsertId()] : []
            );
        } catch (\Exception $e) {
            respuestaJson(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // Muestra la información de una mesa (vista reutilizable para edición).
    public function editar($id)
    {
        Auth::exigirRol(Menu::roles('mesas'));

        $modeloMesa = new Mesa();
        $mesa = $modeloMesa->obtenerPorId((int) $id);
        $modeloArea = new Area();
        $areas = $modeloArea->obtenerTodos();

        require_once __DIR__ . '/../../public/views/mesas/index.php';
    }

    // Actualiza una mesa existente.
    public function actualizar($id)
    {
        Auth::exigirRol(Menu::roles('mesas'));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respuestaJson(['success' => false, 'message' => 'Método no permitido.'], 405);
        }

        if (!Csrf::validar((string) ($_POST['csrf'] ?? ''))) {
            respuestaJson(['success' => false, 'message' => 'Token CSRF inválido.'], 403);
        }

        $numero   = (int) (enteroPositivo($_POST['numero'] ?? null, 65535) ?? 0);
        $capacidad = (int) (enteroPositivo($_POST['capacidad'] ?? null, 255) ?? 0);
        $areaId   = (int) ($_POST['area_id'] ?? 0);
        $estado   = $_POST['estado'] ?? 'disponible';

        $modeloMesa = new Mesa();
        try {
            if ($numero < 1 || $capacidad < 1 || !(new Area())->obtenerPorId($areaId)) {
                respuestaJson(['success' => false, 'message' => 'Número, capacidad y área son obligatorios.'], 400);
            }
            $mesaActual = $modeloMesa->obtenerPorId((int) $id);
            if (!$mesaActual) {
                respuestaJson(['success' => false, 'message' => 'Mesa no encontrada.'], 404);
            }
            $mesaNumero = $modeloMesa->obtenerPorNumero($numero);
            if ($mesaNumero && (int) $mesaNumero['id'] !== (int) $id) {
                respuestaJson(['success' => false, 'message' => 'Ya existe una mesa con ese número.'], 409);
            }
            $ok = $modeloMesa->actualizar((int) $id, $numero, $capacidad, $areaId, $estado);
            respuestaJson(['success' => $ok, 'message' => $ok ? 'Mesa actualizada correctamente.' : 'Mesa no encontrada.'], $ok ? 200 : 404);
        } catch (\Exception $e) {
            respuestaJson(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // Cambia el estado de una mesa (disponible/ocupada/reservada/fuera_servicio).
    public function cambiarEstado($id)
    {
        Auth::exigirRol(Menu::roles('mesas'));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respuestaJson(['success' => false, 'message' => 'Método no permitido.'], 405);
        }

        if (!Csrf::validar((string) ($_POST['csrf'] ?? ''))) {
            respuestaJson(['success' => false, 'message' => 'Token CSRF inválido.'], 403);
        }

        $estado = (string) ($_POST['estado'] ?? 'disponible');
        $modeloMesa = new Mesa();
        try {
            $ok = $modeloMesa->actualizarEstado((int) $id, $estado);
            respuestaJson(['success' => $ok, 'message' => $ok ? 'Estado de mesa actualizado.' : 'Mesa no encontrada.'], $ok ? 200 : 404);
        } catch (\Exception $e) {
            respuestaJson(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // Elimina una mesa.
    public function eliminar($id)
    {
        Auth::exigirRol(Menu::roles('mesas'));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respuestaJson(['success' => false, 'message' => 'Método no permitido.'], 405);
        }

        if (!Csrf::validar((string) ($_POST['csrf'] ?? ''))) {
            respuestaJson(['success' => false, 'message' => 'Token CSRF inválido.'], 403);
        }

        $modeloMesa = new Mesa();
        $ok = $modeloMesa->eliminar((int) $id);
        respuestaJson(['success' => $ok, 'message' => $ok ? 'Mesa eliminada.' : 'Mesa no encontrada.'], $ok ? 200 : 404);
    }
}
