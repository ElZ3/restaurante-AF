<?php

class ReservaController
{
    private function exigirAcceso(): void
    {
        Auth::exigirRol(Menu::roles('reservas'));
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

    /** Normaliza "HH:MM" o "HH:MM:SS" a "HH:MM:SS"; devuelve null si no es válida. */
    private function normalizarHora(string $hora): ?string
    {
        foreach (['!H:i', '!H:i:s'] as $formato) {
            $obj = \DateTime::createFromFormat($formato, $hora);
            if ($obj && $obj->format(substr($formato, 1)) === $hora) {
                return $obj->format('H:i:s');
            }
        }
        return null;
    }

    private function fechaValida(string $fecha): bool
    {
        $obj = \DateTime::createFromFormat('!Y-m-d', $fecha);
        return $obj && $obj->format('Y-m-d') === $fecha;
    }

    private function datosReserva(): array
    {
        $cliente = normalizarEspacios((string) ($_POST['nombre_cliente'] ?? $_POST['cliente'] ?? ''));
        $telefono = trim((string) ($_POST['telefono_cliente'] ?? $_POST['cliente_telefono'] ?? ''));
        $fecha = trim((string) ($_POST['fecha_reserva'] ?? $_POST['fecha'] ?? ''));
        $hora = $this->normalizarHora(trim((string) ($_POST['hora'] ?? '')));
        $personas = enteroPositivo($_POST['personas'] ?? null, 255); // TINYINT UNSIGNED
        $mesaIdValue = trim((string) ($_POST['mesa_id'] ?? ''));
        $mesaId = null;
        if ($mesaIdValue !== '' && $mesaIdValue !== '0') {
            $mesaIdValido = filter_var($mesaIdValue, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($mesaIdValido === false) {
                respuestaJson(['success' => false, 'message' => 'La mesa seleccionada no es válida.'], 400);
            }
            $mesaId = (int) $mesaIdValido;
        }
        $estado = Reserva::estadoABd((string) ($_POST['estado'] ?? 'confirmada'));

        if ($cliente === '' || $telefono === '' || $fecha === '' || $hora === null) {
            respuestaJson(['success' => false, 'message' => 'Completa cliente, teléfono, fecha y hora.'], 400);
        }
        if (!esNombreValido($cliente, 100)) {
            respuestaJson(['success' => false, 'message' => 'El nombre del cliente solo puede contener letras y espacios.'], 400);
        }
        if (!esTelefonoSvValido($telefono)) {
            respuestaJson(['success' => false, 'message' => 'El teléfono debe tener el formato +503 XXXX-XXXX (iniciando en 2, 6 o 7).'], 400);
        }
        if (!$this->fechaValida($fecha)) {
            respuestaJson(['success' => false, 'message' => 'La fecha de la reserva no es válida.'], 400);
        }
        if ($personas === null) {
            respuestaJson(['success' => false, 'message' => 'La cantidad de personas debe ser un número entero mayor a 0.'], 400);
        }
        $observaciones = trim((string) ($_POST['observaciones'] ?? ''));

        return [
            'mesa_id' => $mesaId,
            'cliente' => $cliente,
            'telefono' => $telefono,
            'fecha' => $fecha,
            'hora' => $hora,
            'personas' => $personas,
            'estado' => $estado,
            'observaciones' => $observaciones !== '' ? mb_substr($observaciones, 0, 255) : null,
        ];
    }

    public function index(): void
    {
        $this->exigirAcceso();
        $reservas = (new Reserva())->obtenerTodos();
        $mesas = (new Mesa())->obtenerTodos();
        require __DIR__ . '/../../public/views/reservas/index.php';
    }

    public function mesasDisponibles(): void
    {
        $this->exigirAcceso();
        $fecha = trim((string) ($_GET['fecha'] ?? ''));
        $hora = $this->normalizarHora(trim((string) ($_GET['hora'] ?? '')));
        $personas = enteroPositivo($_GET['personas'] ?? null, 255);
        $reservaId = (int) ($_GET['reserva_id'] ?? 0);
        if (!$this->fechaValida($fecha) || $hora === null || $personas === null) {
            respuestaJson(['success' => false, 'message' => 'Indica fecha, hora y cantidad de personas válidas.'], 400);
        }
        try {
            $mesas = (new Reserva())->obtenerMesasDisponibles($fecha, $hora, $personas, $reservaId);
            respuestaJson(['success' => true, 'mesas' => $mesas]);
        } catch (\PDOException $e) {
            error_log('[Reservas] mesasDisponibles: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudieron consultar las mesas disponibles.'], 500);
        }
    }

    public function crear(): void
    {
        $this->exigirAcceso();
        $this->exigirPostConCsrf();
        $datos = $this->datosReserva();
        try {
            $modelo = new Reserva();
            $id = $modelo->crear(
                $datos['mesa_id'],
                $datos['cliente'],
                $datos['fecha'],
                $datos['hora'],
                $datos['personas'],
                $datos['estado'],
                $datos['telefono'],
                (int) (Auth::usuario()['id'] ?? 0),
                $datos['observaciones']
            );
            respuestaJson(['success' => true, 'message' => 'Reserva creada correctamente.', 'reserva_id' => $id, 'reserva' => $modelo->obtenerPorId($id)], 201);
        } catch (InvalidArgumentException $e) {
            respuestaJson(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\PDOException $e) {
            error_log('[Reservas] crear: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo guardar la reserva (error de base de datos).'], 500);
        } catch (\Throwable $e) {
            error_log('[Reservas] crear: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo guardar la reserva.'], 500);
        }
    }

    public function actualizar($id): void
    {
        $this->exigirAcceso();
        $this->exigirPostConCsrf();
        $datos = $this->datosReserva();
        try {
            $modelo = new Reserva();
            $ok = $modelo->actualizar(
                (int) $id,
                $datos['mesa_id'],
                $datos['cliente'],
                $datos['fecha'],
                $datos['hora'],
                $datos['personas'],
                $datos['estado'],
                $datos['telefono'],
                $datos['observaciones']
            );
            if (!$ok) {
                respuestaJson(['success' => false, 'message' => 'Reserva no encontrada.'], 404);
            }
            respuestaJson(['success' => true, 'message' => 'Reserva actualizada correctamente.', 'reserva_id' => (int) $id, 'reserva' => $modelo->obtenerPorId((int) $id)]);
        } catch (InvalidArgumentException $e) {
            respuestaJson(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\PDOException $e) {
            error_log('[Reservas] actualizar: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo actualizar la reserva (error de base de datos).'], 500);
        } catch (\Throwable $e) {
            error_log('[Reservas] actualizar: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo actualizar la reserva.'], 500);
        }
    }

    public function actualizarEstado($id): void
    {
        $this->exigirAcceso();
        $this->exigirPostConCsrf();
        $estado = (string) ($_POST['estado'] ?? '');
        try {
            $ok = (new Reserva())->actualizarEstado((int) $id, $estado);
            if (!$ok) {
                respuestaJson(['success' => false, 'message' => 'Reserva no encontrada.'], 404);
            }
            respuestaJson(['success' => true, 'message' => 'Estado actualizado.', 'reserva_id' => (int) $id, 'estado' => Reserva::estadoAVista(Reserva::estadoABd($estado))]);
        } catch (InvalidArgumentException $e) {
            respuestaJson(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\PDOException $e) {
            error_log('[Reservas] actualizarEstado: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudo actualizar el estado de la reserva.'], 500);
        }
    }

    public function eliminar($id): void
    {
        $this->exigirAcceso();
        $this->exigirPostConCsrf();
        try {
            $ok = (new Reserva())->eliminar((int) $id);
            respuestaJson(['success' => $ok, 'message' => $ok ? 'Reserva eliminada.' : 'Reserva no encontrada.'], $ok ? 200 : 404);
        } catch (\PDOException $e) {
            error_log('[Reservas] eliminar: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se puede eliminar una reserva vinculada a un pedido.'], 409);
        }
    }
}
