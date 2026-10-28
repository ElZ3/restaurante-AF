<?php
// Controlador: MesaController.php
// Maneja la gestión de mesas del restaurante.
// NOTA (regla temporal de colaboración): la validación de sesión y el
// bloqueo de rutas están programados pero COMENTADOS para que las vistas
// carguen directamente durante el maquetado frontend.

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

        require_once __DIR__ . '/../../public/views/mesas.php';
    }

    // Muestra el formulario para crear una nueva mesa.
    public function nuevo()
    {
        $modeloArea = new Area();
        $areas = $modeloArea->obtenerTodos();

        require_once __DIR__ . '/../../public/views/mesas.php';
    }

    // Guarda una nueva mesa.
    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /mesas');
            exit;
        }

        /*
        // Lógica de creación integrada con modelo Mesa (comentada temporalmente):
        $numero   = $_POST['numero'] ?? '';
        $capacidad= (int) ($_POST['capacidad'] ?? 0);
        $areaId   = (int) ($_POST['area_id'] ?? 0);
        $estado   = $_POST['estado'] ?? 'disponible';

        $modeloMesa = new Mesa();
        $modeloMesa->crear($numero, $capacidad, $areaId, $estado);

        header('Location: /mesas?creada=1');
        exit;
        */

        header('Location: /mesas');
        exit;
    }

    // Muestra el formulario para editar una mesa.
    public function editar($id)
    {
        $modeloMesa = new Mesa();
        $mesa = $modeloMesa->obtenerPorId((int) $id);

        $modeloArea = new Area();
        $areas = $modeloArea->obtenerTodos();

        require_once __DIR__ . '/../../public/views/mesas.php';
    }

    // Actualiza una mesa existente.
    public function actualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /mesas');
            exit;
        }

        /*
        // Lógica de actualización integrada con modelo Mesa (comentada temporalmente):
        $numero   = $_POST['numero'] ?? '';
        $capacidad= (int) ($_POST['capacidad'] ?? 0);
        $areaId   = (int) ($_POST['area_id'] ?? 0);
        $estado   = $_POST['estado'] ?? 'disponible';

        $modeloMesa = new Mesa();
        $modeloMesa->actualizar((int) $id, $numero, $capacidad, $areaId, $estado);

        header('Location: /mesas?actualizada=1');
        exit;
        */

        header('Location: /mesas');
        exit;
    }

    // Cambia el estado de una mesa (disponible/ocupada/reservada).
    public function cambiarEstado($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /mesas');
            exit;
        }

        /*
        // Lógica de cambio de estado (comentada temporalmente):
        $estado = $_POST['estado'] ?? 'disponible';

        $modeloMesa = new Mesa();
        $modeloMesa->actualizarEstado((int) $id, $estado);

        header('Location: /mesas');
        exit;
        */

        header('Location: /mesas');
        exit;
    }

    // Elimina una mesa.
    public function eliminar($id)
    {
        /*
        // Lógica de eliminación integrada con modelo Mesa (comentada temporalmente):
        $modeloMesa = new Mesa();
        $modeloMesa->eliminar((int) $id);

        header('Location: /mesas?eliminada=1');
        exit;
        */

        header('Location: /mesas');
        exit;
    }
}
