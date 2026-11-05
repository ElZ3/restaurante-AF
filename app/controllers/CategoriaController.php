<?php
// Controlador: CategoriaController.php
// CRUD de categorías del menú (tabla: categorias: id, nombre, descripcion, estado).
// Responde JSON, exige rol del módulo Menú y token CSRF.

require_once __DIR__ . '/../models/Categoria.php';

class CategoriaController
{
    private function exigirAcceso(): void
    {
        Auth::exigirRol(Menu::roles('menu'));
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

    /** @return array{0:string,1:string,2:string} */
    private function datos(): array
    {
        $nombre = normalizarEspacios((string) ($_POST['nombre'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $estado = (string) ($_POST['estado'] ?? 'activo');

        if ($nombre === '') {
            respuestaJson(['success' => false, 'message' => 'El nombre de la categoría es obligatorio.'], 400);
        }
        if (!esNombreValido($nombre, 60)) {
            respuestaJson(['success' => false, 'message' => 'El nombre de la categoría solo puede contener letras y espacios (máximo 60).'], 400);
        }
        if (!in_array($estado, ['activo', 'inactivo'], true)) {
            respuestaJson(['success' => false, 'message' => 'Estado de categoría no válido.'], 400);
        }
        return [$nombre, mb_substr($descripcion, 0, 150), $estado];
    }

    public function index()
    {
        $this->exigirAcceso();
        $categorias = (new Categoria())->obtenerTodos();
        $productos = (new Producto())->obtenerTodos();
        require_once __DIR__ . '/../../public/views/menu/index.php';
    }

    public function crear()
    {
        $this->exigirAcceso();
        $this->exigirPostConCsrf();
        [$nombre, $descripcion, $estado] = $this->datos();
        try {
            $ok = (new Categoria())->crear($nombre, $descripcion, $estado);
            $id = $ok ? (int) Database::getInstance()->lastInsertId() : 0;
            respuestaJson(
                ['success' => $ok, 'message' => $ok ? 'Categoría creada correctamente.' : 'No se pudo crear la categoría.'],
                $ok ? 201 : 400,
                $ok ? ['categoria' => ['id' => $id, 'nombre' => $nombre, 'descripcion' => $descripcion, 'estado' => $estado]] : []
            );
        } catch (\PDOException $e) {
            error_log('[Categorias] crear: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo crear la categoría (error de base de datos).'], 500);
        }
    }

    public function editar($id)
    {
        $this->exigirAcceso();
        try {
            $categoria = (new Categoria())->obtenerPorId((int) $id);
            if (!$categoria) {
                respuestaJson(['success' => false, 'message' => 'Categoría no encontrada.'], 404);
            }
            respuestaJson(['success' => true, 'categoria' => $categoria]);
        } catch (\PDOException $e) {
            error_log('[Categorias] editar: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo consultar la categoría.'], 500);
        }
    }

    public function actualizar($id)
    {
        $this->exigirAcceso();
        $this->exigirPostConCsrf();
        [$nombre, $descripcion, $estado] = $this->datos();
        try {
            $modelo = new Categoria();
            if (!$modelo->obtenerPorId((int) $id)) {
                respuestaJson(['success' => false, 'message' => 'Categoría no encontrada.'], 404);
            }
            $modelo->actualizar((int) $id, $nombre, $descripcion, $estado);
            respuestaJson(['success' => true, 'message' => 'Categoría actualizada.']);
        } catch (\PDOException $e) {
            error_log('[Categorias] actualizar: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo actualizar la categoría (error de base de datos).'], 500);
        }
    }

    public function eliminar($id)
    {
        $this->exigirAcceso();
        $this->exigirPostConCsrf();
        try {
            $ok = (new Categoria())->eliminar((int) $id);
            respuestaJson(['success' => $ok, 'message' => $ok ? 'Categoría eliminada.' : 'Categoría no encontrada.'], $ok ? 200 : 404);
        } catch (\PDOException $e) {
            respuestaJson(['success' => false, 'message' => 'No se puede eliminar una categoría que tiene productos.'], 409);
        }
    }
}
