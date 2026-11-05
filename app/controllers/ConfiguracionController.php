<?php

class ConfiguracionController
{
    public function actualizar(): void
    {
        Auth::exigirRol(Menu::roles('config'));
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respuestaJson(['success' => false, 'message' => 'Método no permitido.'], 405);
        }
        if (!Csrf::validar((string) ($_POST['csrf'] ?? ''))) {
            respuestaJson(['success' => false, 'message' => 'Token CSRF inválido.'], 403);
        }

        $valores = [
            'nombre_restaurante' => trim((string) ($_POST['nombre_restaurante'] ?? '')),
            'direccion' => trim((string) ($_POST['direccion'] ?? '')),
            'telefono' => trim((string) ($_POST['telefono'] ?? '')),
            'moneda' => strtoupper(trim((string) ($_POST['moneda'] ?? ''))),
            'tiempo_demora_minutos' => trim((string) ($_POST['tiempo_demora_minutos'] ?? '')),
        ];
        if ($valores['nombre_restaurante'] === ''
            || !preg_match('/^[A-Z]{3}$/', $valores['moneda'])
            || !ctype_digit($valores['tiempo_demora_minutos'])
            || (int) $valores['tiempo_demora_minutos'] < 1
            || (int) $valores['tiempo_demora_minutos'] > 240) {
            respuestaJson(['success' => false, 'message' => 'Verifica el nombre, la moneda (código de 3 letras) y el tiempo de alerta (1 a 240 minutos).'], 400);
        }

        $pdo = Database::getInstance()->getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO configuracion (clave, valor) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE valor = VALUES(valor)'
            );
            foreach ($valores as $clave => $valor) {
                $stmt->execute([$clave, $valor]);
            }
            $pdo->commit();
            respuestaJson(['success' => true, 'message' => 'Configuración guardada.']);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            respuestaJson(['success' => false, 'message' => 'No se pudo guardar la configuración.'], 500);
        }
    }
}
