<?php
// Controlador: UsuarioController.php
// Maneja el CRUD de usuarios del sistema.
// NOTA (regla temporal de colaboración): la validación de sesión, el
// bloqueo de rutas y las consultas a la base de datos están programadas
// pero COMENTADAS para que las vistas carguen directamente durante el
// maquetado frontend, sin requerir conexión a la base de datos local.

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Rol.php';

class UsuarioController
{
    // Muestra el listado de usuarios.
    public function index()
    {
        /*
        // Validación de sesión y rol (comentada temporalmente):
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login');
            exit;
        }
        if ($_SESSION['rol'] !== 'administrador') {
            header('Location: /dashboard?error=acceso_denegado');
            exit;
        }
        */

        $modeloUsuario = new Usuario();
        $usuarios = $modeloUsuario->obtenerTodos();

        require_once __DIR__ . '/../../public/views/usuarios.php';
    }

    // Muestra el formulario para crear un nuevo usuario.
    public function nuevo()
    {
        $modeloRol = new Rol();
        $roles = $modeloRol->obtenerTodos();

        require_once __DIR__ . '/../../public/views/usuarios.php';
    }

    // Guarda un nuevo usuario.
    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /usuarios');
            exit;
        }

        /*
        // Lógica de creación integrada con modelo Usuario (comentada temporalmente):
        $nombre     = $_POST['nombre'] ?? '';
        $usuario    = $_POST['usuario'] ?? $_POST['email'] ?? '';
        $rolId      = (int) ($_POST['rol_id'] ?? 0);
        $contrasena = $_POST['contrasena'] ?? '';
        $estado     = $_POST['estado'] ?? 'activo';

        $modeloUsuario = new Usuario();
        $modeloUsuario->crear($nombre, $usuario, $contrasena, $rolId, $estado);

        header('Location: /usuarios?creado=1');
        exit;
        */

        header('Location: /usuarios');
        exit;
    }

    // Muestra el formulario para editar un usuario.
    public function editar($id)
    {
        /*
        // Consulta integrada con modelo Usuario (comentada temporalmente):
        $modeloUsuario = new Usuario();
        $usuario = $modeloUsuario->obtenerPorId((int) $id);
        */

        $usuario = [];
        require_once __DIR__ . '/../../public/views/usuarios.php';
    }

    // Actualiza un usuario existente.
    public function actualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /usuarios');
            exit;
        }

        /*
        // Lógica de actualización integrada con modelo Usuario (comentada temporalmente):
        $nombre   = $_POST['nombre'] ?? '';
        $usuario  = $_POST['usuario'] ?? $_POST['email'] ?? '';
        $rolId    = (int) ($_POST['rol_id'] ?? 0);
        $estado   = $_POST['estado'] ?? 'activo';

        $modeloUsuario = new Usuario();
        $modeloUsuario->actualizar((int) $id, $nombre, $usuario, $rolId, $estado);

        header('Location: /usuarios?actualizado=1');
        exit;
        */

        header('Location: /usuarios');
        exit;
    }

    // Elimina un usuario.
    public function eliminar($id)
    {
        /*
        // Lógica de eliminación integrada con modelo Usuario (comentada temporalmente):
        $modeloUsuario = new Usuario();
        $modeloUsuario->eliminar((int) $id);

        header('Location: /usuarios?eliminado=1');
        exit;
        */

        header('Location: /usuarios');
        exit;
    }
}
