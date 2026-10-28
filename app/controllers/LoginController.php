<?php
// Controlador: LoginController.php
// Maneja la autenticación de usuarios del sistema.
// NOTA (regla temporal de colaboración): la validación de sesión y el
// bloqueo de rutas están programados pero COMENTADOS para que las vistas
// carguen directamente durante el maquetado frontend, sin requerir conexión
// a la base de datos local.

class LoginController
{
    // Muestra el formulario de inicio de sesión.
    public function index()
    {
        /*
        // Validación de sesión (comentada temporalmente):
        if (isset($_SESSION['usuario_id'])) {
            header('Location: /dashboard');
            exit;
        }
        */

        require_once __DIR__ . '/../views/login.php';
    }

    // Procesa el intento de autenticación.
    public function autenticar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $usuario = $_POST['usuario'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';

        /*
        // Lógica de autenticación (comentada temporalmente):
        $db = require __DIR__ . '/../config/database.php';
        $pdo = new PDO(
            "mysql:host={$db['host']};dbname={$db['dbname']};charset={$db['charset']}",
            $db['username'],
            $db['password'],
            $db['options']
        );

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND estado = 'activo'");
        $stmt->execute([$usuario]);
        $usuarioEncontrado = $stmt->fetch();

        if ($usuarioEncontrado && password_verify($contrasena, $usuarioEncontrado['contrasena'])) {
            session_start();
            $_SESSION['usuario_id'] = $usuarioEncontrado['id'];
            $_SESSION['usuario'] = $usuarioEncontrado['usuario'];
            $_SESSION['rol'] = $usuarioEncontrado['rol'];
            header('Location: /dashboard');
            exit;
        } else {
            header('Location: /login?error=1');
            exit;
        }
        */

        // Render directo de la vista (maquetado frontend sin DB).
        require_once __DIR__ . '/../views/login.php';
    }

    // Cierra la sesión del usuario.
    public function logout()
    {
        /*
        // Cierre de sesión (comentado temporalmente):
        session_start();
        session_destroy();
        */

        header('Location: /login');
        exit;
    }
}
