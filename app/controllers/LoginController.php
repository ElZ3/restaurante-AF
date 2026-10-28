<?php
// Controlador: LoginController.php
// Maneja la autenticación de usuarios del sistema.
// Utiliza el sistema Auth de Olivo Real Restaurant (app/core/Auth.php).

require_once __DIR__ . '/../models/Usuario.php';

class LoginController
{
    // Muestra el formulario de inicio de sesión.
    public function index()
    {
        // Validación de sesión: si ya está autenticado, redirigir al dashboard.
        if (Auth::usuario() !== null) {
            redirigir('/dashboard');
        }

        $modo_demo = defined('MODO_DEMO') && MODO_DEMO;
        require_once __DIR__ . '/../../public/views/login.php';
    }

    // Procesa el intento de autenticación.
    public function autenticar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('/login');
        }

        if (isset($_POST['demo'])) {
            if (Auth::iniciarDemo($_POST['demo_rol'] ?? '')) {
                redirigir('/dashboard');
            }
            redirigir('/login?demo=desactivado');
        }

        $usuario = trim((string) ($_POST['usuario'] ?? $_POST['email'] ?? ''));
        $contrasena = (string) ($_POST['contrasena'] ?? $_POST['password'] ?? '');

        $modeloUsuario = new Usuario();
        $usuarioEncontrado = $modeloUsuario->obtenerPorNombreUsuario($usuario);

        $hash = is_array($usuarioEncontrado)
            ? (string) ($usuarioEncontrado['password'] ?? $usuarioEncontrado['contrasena'] ?? '')
            : '';
        if ($usuarioEncontrado && $hash !== '' && $modeloUsuario->verificarContrasena($contrasena, $hash)) {
            Auth::iniciarSesion([
                'id'     => $usuarioEncontrado['id'],
                'nombre' => $usuarioEncontrado['nombre'],
                'rol'    => $usuarioEncontrado['rol_nombre'] ?? $usuarioEncontrado['rol'] ?? $usuarioEncontrado['rol_id'] ?? '',
            ]);
            redirigir('/dashboard');
        } else {
            redirigir('/login?error=1');
        }
    }

    // Cierra la sesión del usuario.
    public function logout()
    {
        Auth::cerrar();
        redirigir('/login');
    }
}
