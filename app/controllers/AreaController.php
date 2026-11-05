<?php

class AreaController
{
    private function validarSolicitud(): void
    {
        Auth::exigirRol(Menu::roles('config'));
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respuestaJson(['success' => false, 'message' => 'Método no permitido.'], 405);
        }
        if (!Csrf::validar((string) ($_POST['csrf'] ?? ''))) {
            respuestaJson(['success' => false, 'message' => 'Token CSRF inválido.'], 403);
        }
    }

    public function crear(): void
    {
        $this->validarSolicitud();
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        if ($nombre === '') {
            respuestaJson(['success' => false, 'message' => 'El nombre del área es obligatorio.'], 400);
        }
        $ok = (new Area())->crear($nombre, $descripcion);
        respuestaJson(
            ['success' => $ok, 'message' => $ok ? 'Área creada.' : 'No se pudo crear el área.', 'area_id' => (int) Database::getInstance()->lastInsertId()],
            $ok ? 201 : 400
        );
    }

    public function actualizar($id): void
    {
        $this->validarSolicitud();
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        if ($nombre === '') {
            respuestaJson(['success' => false, 'message' => 'El nombre del área es obligatorio.'], 400);
        }
        $modelo = new Area();
        if (!$modelo->obtenerPorId((int) $id)) {
            respuestaJson(['success' => false, 'message' => 'Área no encontrada.'], 404);
        }
        $ok = $modelo->actualizar((int) $id, $nombre, $descripcion);
        respuestaJson(['success' => $ok, 'message' => $ok ? 'Área actualizada.' : 'No se pudo actualizar el área.']);
    }

    public function eliminar($id): void
    {
        $this->validarSolicitud();
        try {
            $ok = (new Area())->eliminar((int) $id);
            respuestaJson(['success' => $ok, 'message' => $ok ? 'Área eliminada.' : 'Área no encontrada.'], $ok ? 200 : 404);
        } catch (\PDOException $e) {
            respuestaJson(['success' => false, 'message' => 'No se puede eliminar un área que todavía tiene mesas asignadas.'], 409);
        }
    }
}
