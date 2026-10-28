<?php
// Controlador: UsuarioController.php
// Maneja el CRUD de usuarios del sistema.
// NOTA (regla temporal de colaboración): la validación de sesión, el
// bloqueo de rutas y las consultas a la base de datos están programadas
// pero COMENTADAS para que las vistas carguen directamente durante el
// maquetado frontend, sin requerir conexión a la base de datos local.

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

        /*
        // Consulta a base de datos (comentada temporalmente):
        $db = require __DIR__ . '/../config/database.php';
        $pdo = new PDO(
            "mysql:host={$db['host']};dbname={$db['dbname']};charset={$db['charset']}",
            $db['username'],
            $db['password'],
            $db['options']
        );
        $stmt = $pdo->query("SELECT id, nombre, usuario, rol, estado FROM usuarios ORDER BY id DESC");
        $usuarios = $stmt->fetchAll();
        */

        $usuarios = [];
        require_once __DIR__ . '/../views/usuarios.php';
    }

    // Muestra el formulario para crear un nuevo usuario.
    public function nuevo()
    {
        require_once __DIR__ . '/../views/usuarios.php';
    }

    // Guarda un nuevo usuario.
    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /usuarios');
            exit;
        }

        /*
        // Lógica de creación (comentada temporalmente):
        $nombre     = $_POST['nombre'] ?? '';
        $usuario    = $_POST['usuario'] ?? '';
        $rol        = $_POST['rol'] ?? 'mesero';
        $contrasena = password_hash($_POST['contrasena'] ?? '', PASSWORD_DEFAULT);

        $db = require __DIR__ . '/../config/database.php';
        $pdo = new PDO(...);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, usuario, rol, contrasena, estado) VALUES (?, ?, ?, ?, 'activo')");
        $stmt->execute([$nombre, $usuario, $rol, $contrasena]);

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
        $db = require __DIR__ . '/../config/database.php';
        $pdo = new PDO(...);
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        */

        $usuario = [];
        require_once __DIR__ . '/../views/usuarios.php';
    }

    // Actualiza un usuario existente.
    public function actualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /usuarios');
            exit;
        }

        /*
        // Lógica de actualización (comentada temporalmente):
        $db = require __DIR__ . '/../config/database.php';
        $pdo = new PDO(...);
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, usuario = ?, rol = ?, estado = ? WHERE id = ?");
        $stmt->execute([$_POST['nombre'], $_POST['usuario'], $_POST['rol'], $_POST['estado'], $id]);

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
        // Lógica de eliminación (comentada temporalmente):
        $db = require __DIR__ . '/../config/database.php';
        $pdo = new PDO(...);
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);

        header('Location: /usuarios?eliminado=1');
        exit;
        */

        header('Location: /usuarios');
        exit;
    }
}
