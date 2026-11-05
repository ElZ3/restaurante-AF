<?php

class UsuarioController
{
    private function exigirAdmin(): void
    {
        Auth::exigirRol(Menu::roles('usuarios'));
    }

    private function validarPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respuestaJson(['success' => false, 'message' => 'Método no permitido.'], 405);
        }
        if (!Csrf::validar((string) ($_POST['csrf'] ?? ''))) {
            respuestaJson(['success' => false, 'message' => 'Token CSRF inválido.'], 403);
        }
    }

    private function datos(): array
    {
        $nombre = normalizarEspacios((string) ($_POST['nombre'] ?? ''));
        if ($nombre !== '' && !esNombreValido($nombre, 100)) {
            respuestaJson(['success' => false, 'message' => 'El nombre solo puede contener letras y espacios.'], 400);
        }
        $correo = strtolower(trim((string) ($_POST['correo'] ?? '')));
        $rol = (string) ($_POST['rol'] ?? '');
        $estado = (string) ($_POST['estado'] ?? 'activo');
        if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)
            || !in_array($rol, ['admin', 'mesero', 'cocina', 'cajero'], true)
            || !in_array($estado, ['activo', 'inactivo'], true)) {
            respuestaJson(['success' => false, 'message' => 'Completa nombre, correo, rol y estado válidos.'], 400);
        }
        return [$nombre, $correo, $rol, $estado];
    }

    public function index(): void
    {
        $this->exigirAdmin();
        $usuarios = (new Usuario())->obtenerTodos();
        require __DIR__ . '/../../public/views/usuarios/index.php';
    }

    public function nuevo(): void
    {
        $this->index();
    }

    public function crear(): void
    {
        $this->exigirAdmin();
        $this->validarPost();
        [$nombre, $correo, $rol, $estado] = $this->datos();
        $contrasena = (string) ($_POST['contrasena'] ?? '');
        if (strlen($contrasena) < 10) {
            respuestaJson(['success' => false, 'message' => 'La contraseña debe tener al menos 10 caracteres.'], 400);
        }
        try {
            $ok = (new Usuario())->crear($nombre, $correo, $contrasena, $rol, $estado);
            if (!$ok) {
                respuestaJson(['success' => false, 'message' => 'No se pudo crear el usuario.'], 400);
            }
            respuestaJson([
                'success' => true,
                'message' => 'Usuario creado correctamente.',
                'usuario' => (new Usuario())->obtenerPorId((int) Database::getInstance()->lastInsertId()),
            ], 201);
        } catch (\PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                respuestaJson(['success' => false, 'message' => 'Ya existe una cuenta con ese correo.'], 409);
            }
            respuestaJson(['success' => false, 'message' => 'No se pudo crear el usuario.'], 500);
        }
    }

    public function editar($id): void
    {
        $this->exigirAdmin();
        $usuario = (new Usuario())->obtenerPorId((int) $id);
        if (!$usuario) {
            http_response_code(404);
            exit('Usuario no encontrado.');
        }
        $usuarios = (new Usuario())->obtenerTodos();
        require __DIR__ . '/../../public/views/usuarios/index.php';
    }

    public function actualizar($id): void
    {
        $this->exigirAdmin();
        $this->validarPost();
        [$nombre, $correo, $rol, $estado] = $this->datos();
        $modelo = new Usuario();
        $usuario = $modelo->obtenerPorId((int) $id);
        if (!$usuario) {
            respuestaJson(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        }
        if ((int) $id === (int) (Auth::usuario()['id'] ?? 0) && $estado !== 'activo') {
            respuestaJson(['success' => false, 'message' => 'No puedes desactivar tu propia cuenta.'], 400);
        }
        $contrasena = (string) ($_POST['contrasena'] ?? '');
        if ($contrasena !== '' && strlen($contrasena) < 10) {
            respuestaJson(['success' => false, 'message' => 'La contraseña debe tener al menos 10 caracteres.'], 400);
        }
        try {
            $modelo->actualizar((int) $id, $nombre, $correo, $rol, $estado);
            if ($contrasena !== '') {
                $modelo->actualizarContrasena((int) $id, $contrasena);
            }
            respuestaJson([
                'success' => true,
                'message' => 'Usuario actualizado correctamente.',
                'usuario' => $modelo->obtenerPorId((int) $id),
            ]);
        } catch (\PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                respuestaJson(['success' => false, 'message' => 'Ya existe una cuenta con ese correo.'], 409);
            }
            respuestaJson(['success' => false, 'message' => 'No se pudo actualizar el usuario.'], 500);
        }
    }

    public function eliminar($id): void
    {
        $this->exigirAdmin();
        $this->validarPost();
        if ((int) $id === (int) (Auth::usuario()['id'] ?? 0)) {
            respuestaJson(['success' => false, 'message' => 'No puedes desactivar tu propia cuenta.'], 400);
        }
        $ok = (new Usuario())->eliminar((int) $id);
        respuestaJson(['success' => $ok, 'message' => $ok ? 'Usuario desactivado.' : 'Usuario no encontrado.'], $ok ? 200 : 404);
    }
}
