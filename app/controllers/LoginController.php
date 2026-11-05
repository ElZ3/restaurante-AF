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

        $correo = trim((string) ($_POST['usuario'] ?? $_POST['email'] ?? ''));
        $contrasena = (string) ($_POST['contrasena'] ?? $_POST['password'] ?? '');

        // Bloqueo por fuerza bruta: consultar intentos_login
        $pdo = Database::getInstance()->getConnection();
        $ventana = date('Y-m-d H:i:s', strtotime('-15 minutes'));
        $stmtInt = $pdo->prepare(
            "SELECT COUNT(*) AS total FROM intentos_login WHERE correo = ? AND fecha > ?"
        );
        $stmtInt->execute([$correo, $ventana]);
        $intento = $stmtInt->fetch();
        if ($intento && (int) $intento['total'] >= 3) {
            redirigir('/login?error=bloqueado');
        }

        // Autenticar con modelo Usuario (usa correo y password_hash de olivo_real.sql)
        $modeloUsuario = new Usuario();
        $usuarioEncontrado = $modeloUsuario->autenticar($correo, $contrasena);

        if ($usuarioEncontrado) {
            // Éxito: limpiar intentos fallidos y crear sesión
            $pdo->prepare("DELETE FROM intentos_login WHERE correo = ?")->execute([$correo]);
            Auth::iniciarSesion([
                'id'    => $usuarioEncontrado['id'],
                'nombre'=> $usuarioEncontrado['nombre'],
                'rol'   => $usuarioEncontrado['rol'],
            ]);
            redirigir('/dashboard');
        } else {
            // Fallo: registrar intento fallido
            $pdo->prepare(
                "INSERT INTO intentos_login (correo, ip, fecha) VALUES (?, ?, NOW())"
            )->execute([$correo, $_SERVER['REMOTE_ADDR'] ?? '']);
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
