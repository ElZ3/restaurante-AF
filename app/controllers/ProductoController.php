<?php
// Controlador: ProductoController.php
// CRUD de productos del menú (tabla: productos).
// Imagen: acepta un archivo subido en el campo `imagen` (multipart) con extensión
// .jpg/.jpeg/.png/.webp, o bien una ruta de texto dentro de /img/menu/.
// Se guarda en public/img/menu/ y el nombre del archivo va a productos.imagen.

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Categoria.php';

class ProductoController
{
    private const IMG_MAX_BYTES = 5 * 1024 * 1024; // 5 MB
    private const IMG_EXTENSIONES = ['jpg', 'jpeg', 'png', 'webp'];
    private const IMG_MIMES = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png'  => ['png'],
        'image/webp' => ['webp'],
    ];
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

    private function directorioImagenes(): string
    {
        return dirname(__DIR__, 2) . '/public/img/menu/';
    }

    /**
     * Procesa la imagen del producto.
     * Devuelve el nombre del archivo que se guardará en productos.imagen ('' si no hay imagen).
     * Responde 400 en JSON si el archivo no es válido.
     */
    private function procesarImagen(): string
    {
        $archivo = $_FILES['imagen'] ?? null;

        if (is_array($archivo) && ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $error = (int) $archivo['error'];
            if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
                respuestaJson(['success' => false, 'message' => 'La imagen supera el tamaño permitido (5 MB).'], 400);
            }
            if ($error !== UPLOAD_ERR_OK || !is_uploaded_file((string) $archivo['tmp_name'])) {
                respuestaJson(['success' => false, 'message' => 'No se pudo recibir la imagen.'], 400);
            }
            if ((int) $archivo['size'] > self::IMG_MAX_BYTES) {
                respuestaJson(['success' => false, 'message' => 'La imagen supera el tamaño permitido (5 MB).'], 400);
            }

            $extension = strtolower(pathinfo((string) $archivo['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, self::IMG_EXTENSIONES, true)) {
                respuestaJson(['success' => false, 'message' => 'Formato no permitido. Usa .jpg, .jpeg, .png o .webp.'], 400);
            }

            // Validar el contenido real del archivo (no solo la extensión).
            $mime = '';
            if (class_exists('finfo')) {
                $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
            }
            if ($mime === '') {
                $info = @getimagesize($archivo['tmp_name']);
                $mime = is_array($info) ? (string) ($info['mime'] ?? '') : '';
            }
            if (!isset(self::IMG_MIMES[$mime]) || !in_array($extension, self::IMG_MIMES[$mime], true)) {
                respuestaJson(['success' => false, 'message' => 'El archivo no es una imagen válida.'], 400);
            }

            $directorio = $this->directorioImagenes();
            if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
                respuestaJson(['success' => false, 'message' => 'No existe la carpeta de imágenes en el servidor.'], 500);
            }
            if (!is_writable($directorio)) {
                respuestaJson(['success' => false, 'message' => 'La carpeta de imágenes no tiene permisos de escritura.'], 500);
            }

            $nombre = 'prod_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
            if (!move_uploaded_file($archivo['tmp_name'], $directorio . $nombre)) {
                respuestaJson(['success' => false, 'message' => 'No se pudo guardar la imagen en el servidor.'], 500);
            }
            @chmod($directorio . $nombre, 0644);

            return $nombre;
        }

        // Sin archivo: se acepta la ruta de texto que envía el formulario actual.
        $ruta = trim((string) ($_POST['imagen'] ?? ''));
        if ($ruta !== '' && !preg_match('#^(?:/img/menu/)?[A-Za-z0-9_-]+\.(?:png|jpe?g|webp)$#i', $ruta)) {
            respuestaJson(['success' => false, 'message' => 'La imagen debe ser un archivo PNG, JPG, JPEG o WEBP dentro de /img/menu/.'], 400);
        }
        return $ruta === '' ? '' : basename($ruta);
    }

    /** Borra del disco una imagen subida por el sistema (prefijo prod_) que ya no se usa. */
    private function borrarImagenSubida(?string $ruta): void
    {
        $nombre = $ruta ? basename($ruta) : '';
        if ($nombre === '' || !preg_match('#^(prod_[A-Za-z0-9_]+\.(jpe?g|png|webp))$#i', $nombre, $m)) {
            return;
        }
        $archivo = $this->directorioImagenes() . $m[1];
        if (is_file($archivo)) {
            @unlink($archivo);
        }
    }

    /** @return array{0:string,1:string,2:float,3:int,4:bool} */
    private function datosProducto(): array
    {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $precio = decimalPositivo($_POST['precio'] ?? '');
        $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
        $disponible = in_array((string) ($_POST['disponible'] ?? '0'), ['1', 'true', 'on'], true);
        if ($nombre === '') {
            respuestaJson(['success' => false, 'message' => 'El nombre del producto es obligatorio.'], 400);
        }
        if ($categoriaId < 1) {
            respuestaJson(['success' => false, 'message' => 'Selecciona una categoría activa.'], 400);
        }
        if ($precio === null) {
            respuestaJson(['success' => false, 'message' => 'El precio debe ser un número mayor a 0 (máximo 2 decimales).'], 400);
        }
        return [mb_substr($nombre, 0, 100), mb_substr($descripcion, 0, 255), (float) $precio, $categoriaId, $disponible];
    }

    private function validarCategoriaActiva(int $categoriaId): void
    {
        if (!(new Categoria())->obtenerActivaPorId($categoriaId)) {
            respuestaJson(['success' => false, 'message' => 'Selecciona una categoría activa.'], 400);
        }
    }

    public function index()
    {
        $this->exigirAcceso();
        $productos = (new Producto())->obtenerTodos();
        $categorias = (new Categoria())->obtenerActivas();
        require_once __DIR__ . '/../../public/views/menu/index.php';
    }

    public function nuevo()
    {
        $this->exigirAcceso();
        $categorias = (new Categoria())->obtenerActivas();
        require_once __DIR__ . '/../../public/views/menu/index.php';
    }

    public function listar(): void
    {
        $this->exigirAcceso();
        $categoriaId = null;
        if (isset($_GET['categoria_id']) && $_GET['categoria_id'] !== '') {
            $categoriaId = filter_var($_GET['categoria_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($categoriaId === false) {
                respuestaJson(['success' => false, 'message' => 'La categoría seleccionada no es válida.'], 400);
            }
        }
        try {
            if ($categoriaId !== null) {
                $this->validarCategoriaActiva((int) $categoriaId);
            }
            $productos = (new Producto())->obtenerTodos($categoriaId === null ? null : (int) $categoriaId);
            respuestaJson(['success' => true, 'productos' => $productos]);
        } catch (\PDOException $e) {
            error_log('[Productos] listar: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudieron cargar los productos.'], 500);
        }
    }

    public function crear()
    {
        $this->exigirAcceso();
        $this->exigirPostConCsrf();
        [$nombre, $descripcion, $precio, $categoriaId, $disponible] = $this->datosProducto();
        $imagen = '';

        try {
            $this->validarCategoriaActiva($categoriaId);
            $imagen = $this->procesarImagen();
            $modelo = new Producto();
            $ok = $modelo->crear($nombre, $descripcion, $precio, $categoriaId, $disponible, $imagen);
            $id = $ok ? (int) Database::getInstance()->lastInsertId() : 0;
            if (!$ok) {
                $this->borrarImagenSubida($imagen);
            }
            respuestaJson(
                ['success' => $ok, 'message' => $ok ? 'Producto creado correctamente.' : 'No se pudo crear el producto.'],
                $ok ? 201 : 400,
                $ok ? ['producto_id' => $id, 'imagen' => $imagen] : []
            );
        } catch (\PDOException $e) {
            $this->borrarImagenSubida($imagen);
            error_log('[Productos] crear: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo crear el producto (error de base de datos).'], 500);
        }
    }

    public function editar($id)
    {
        $this->exigirAcceso();
        $producto = (new Producto())->obtenerPorId((int) $id);
        $categorias = (new Categoria())->obtenerActivas();
        require_once __DIR__ . '/../../public/views/menu/index.php';
    }

    public function actualizar($id)
    {
        $this->exigirAcceso();
        $this->exigirPostConCsrf();
        [$nombre, $descripcion, $precio, $categoriaId, $disponible] = $this->datosProducto();
        $imagen = '';

        try {
            $this->validarCategoriaActiva($categoriaId);
            $modelo = new Producto();
            $actual = $modelo->obtenerPorId((int) $id);
            if (!$actual) {
                respuestaJson(['success' => false, 'message' => 'Producto no encontrado.'], 404);
            }
            $imagen = $this->procesarImagen();
            $modelo->actualizar((int) $id, $nombre, $descripcion, $precio, $categoriaId, $disponible, $imagen);
            if (($actual['imagen'] ?? '') !== $imagen) {
                $this->borrarImagenSubida($actual['imagen'] ?? null);
            }
            respuestaJson(['success' => true, 'message' => 'Producto actualizado.', 'imagen' => $imagen]);
        } catch (\PDOException $e) {
            $this->borrarImagenSubida($imagen);
            error_log('[Productos] actualizar: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo actualizar el producto (error de base de datos).'], 500);
        }
    }

    public function eliminar($id)
    {
        $this->exigirAcceso();
        $this->exigirPostConCsrf();

        try {
            $modelo = new Producto();
            $ok = $modelo->eliminar((int) $id);
            respuestaJson(['success' => $ok, 'message' => $ok ? 'Producto eliminado.' : 'Producto no encontrado.'], $ok ? 200 : 404);
        } catch (\PDOException $e) {
            respuestaJson(['success' => false, 'message' => 'No se puede eliminar un producto incluido en un pedido.'], 409);
        }
    }

    public function toggle($id)
    {
        $this->exigirAcceso();
        $this->exigirPostConCsrf();

        $disponible = filter_var($_POST['disponible'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($disponible === null) {
            respuestaJson(['success' => false, 'message' => 'Disponibilidad no válida.'], 400);
        }
        try {
            $ok = (new Producto())->toggleDisponible((int) $id, $disponible);
            respuestaJson(['success' => $ok, 'message' => $ok ? 'Disponibilidad actualizada.' : 'Producto no encontrado.', 'disponible' => $disponible]);
        } catch (\PDOException $e) {
            error_log('[Productos] toggle: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo actualizar la disponibilidad.'], 500);
        }
    }
}
