<?php

require_once __DIR__ . '/../core/Menu.php';

class DashboardController
{
    public function index()
    {
        Auth::exigirRol(Menu::roles('dashboard'));

        $mesas = (new Mesa())->obtenerTodos();
        $ordenes = (new Orden())->obtenerTodos();
        $reservas = (new Reserva())->obtenerPorFecha(date('Y-m-d'));
        $normalizarEstado = static function ($estado) {
            return strtolower(str_replace(
                [' ', 'á', 'é', 'í', 'ó', 'ú'],
                ['_', 'a', 'e', 'i', 'o', 'u'],
                trim((string) $estado)
            ));
        };

        $mesasActivas = array_filter($mesas, static function ($mesa) {
            return (int) ($mesa['activo'] ?? 1) === 1;
        });
        $mesas_total = count($mesasActivas);
        $mesas_ocupadas = count(array_filter($mesasActivas, static function ($mesa) use ($normalizarEstado) {
            return $normalizarEstado($mesa['estado'] ?? '') === 'ocupada';
        }));

        $categoriasEstado = [
            'abierta' => 'pendiente',
            'en_preparacion' => 'preparacion',
            'servida' => 'lista',
            'pagada' => 'entregada',
        ];
        $conteosEstado = [
            'pendiente' => ['Pendientes', 0, 'pendiente'],
            'preparacion' => ['En preparación', 0, 'preparacion'],
            'lista' => ['Listos', 0, 'lista'],
            'entregada' => ['Entregados', 0, 'entregada'],
        ];

        $diasSemana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        $ventas_semana = [];
        $fechasSemana = [];
        for ($desfase = -6; $desfase <= 0; $desfase++) {
            $fecha = date('Y-m-d', strtotime($desfase . ' days'));
            $clave = $diasSemana[(int) date('N', strtotime($fecha)) - 1];
            $ventas_semana[$clave] = 0.0;
            $fechasSemana[$fecha] = $clave;
        }

        $ventas_hoy = 0.0;
        $pedidos_cocina = 0;
        $estadosCompletados = ['pagada'];
        foreach ($ordenes as $orden) {
            $estado = $normalizarEstado($orden['estado'] ?? '');
            if (isset($categoriasEstado[$estado])) {
                $conteosEstado[$categoriasEstado[$estado]][1]++;
            }
            if (in_array($estado, ['abierta', 'en_preparacion'], true)) {
                $pedidos_cocina++;
            }

            $marcaTiempo = strtotime((string) ($orden['fecha_hora'] ?? ''));
            if ($marcaTiempo === false || !in_array($estado, $estadosCompletados, true)) {
                continue;
            }
            $fechaOrden = date('Y-m-d', $marcaTiempo);
            $totalOrden = (float) ($orden['total'] ?? 0);
            if ($fechaOrden === date('Y-m-d')) {
                $ventas_hoy += $totalOrden;
            }
            if (isset($fechasSemana[$fechaOrden])) {
                $ventas_semana[$fechasSemana[$fechaOrden]] += $totalOrden;
            }
        }

        $pedidos_estado = array_values($conteosEstado);
        $reservas_hoy = [];
        $reservas_prox = 0;
        foreach ($reservas as $reserva) {
            $estado = $normalizarEstado($reserva['estado'] ?? 'pendiente');
            $hora = (string) ($reserva['hora_reserva'] ?? '');
            $marcaTiempo = strtotime(date('Y-m-d') . ' ' . $hora);
            if ($marcaTiempo === false || $marcaTiempo < time() || $estado === 'cancelada') {
                continue;
            }
            $reservas_prox++;
            $reservas_hoy[] = [
                date('H:i', $marcaTiempo),
                $reserva['cliente_nombre'] ?? 'Cliente',
                'Mesa ' . ($reserva['mesa_numero'] ?? $reserva['mesa_id'] ?? '—'),
                $estado === 'confirmada' ? 'confirmada' : ($estado === 'completada' ? 'atendida' : 'pendiente'),
            ];
        }

        require_once __DIR__ . '/../../public/views/dashboard.php';
    }
}
