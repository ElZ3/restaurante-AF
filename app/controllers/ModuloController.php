<?php

require_once __DIR__ . '/../core/Menu.php';

class ModuloController
{
    public function menu()
    {
        Auth::exigirRol(Menu::roles('menu'));

        $productos = (new Producto())->obtenerTodos();
        require __DIR__ . '/../../public/views/menu.php';
    }

    public function pedidos()
    {
        Auth::exigirRol(Menu::roles('pedidos'));

        $pedidos = $this->obtenerPedidosConDetalles();
        require __DIR__ . '/../../public/views/pedidos.php';
    }

    public function cocina()
    {
        Auth::exigirRol(Menu::roles('cocina'));

        $pedidos = $this->obtenerPedidosConDetalles();
        require __DIR__ . '/../../public/views/cocina.php';
    }

    public function reservas()
    {
        Auth::exigirRol(Menu::roles('reservas'));

        $reservas = (new Reserva())->obtenerTodos();
        require __DIR__ . '/../../public/views/reservas.php';
    }

    public function configuracion()
    {
        Auth::exigirRol(Menu::roles('config'));

        $areas = (new Area())->obtenerTodos();
        $mesas = (new Mesa())->obtenerTodos();
        require __DIR__ . '/../../public/views/configuracion.php';
    }

    public function reportes()
    {
        Auth::exigirRol(Menu::roles('reportes'));

        $ordenes = (new Orden())->obtenerTodos();
        $dias = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        $fechas = [];
        $ventas_semana = [];
        for ($desfase = -6; $desfase <= 0; $desfase++) {
            $fecha = date('Y-m-d', strtotime($desfase . ' days'));
            $etiqueta = $dias[(int) date('N', strtotime($fecha)) - 1];
            $fechas[$fecha] = $etiqueta;
            $ventas_semana[$etiqueta] = 0.0;
        }

        $ventas = 0.0;
        $cantidadPedidos = 0;
        foreach ($ordenes as $orden) {
            $estado = $this->normalizarEstado($orden['estado'] ?? '');
            if (!in_array($estado, ['entregada', 'entregado', 'pagada', 'pagado', 'completada', 'completado'], true)) {
                continue;
            }
            $marcaTiempo = strtotime((string) ($orden['fecha_hora'] ?? ''));
            if ($marcaTiempo === false) {
                continue;
            }
            $fechaOrden = date('Y-m-d', $marcaTiempo);
            if (!isset($fechas[$fechaOrden])) {
                continue;
            }
            $totalOrden = (float) ($orden['total'] ?? 0);
            $ventas += $totalOrden;
            $ventas_semana[$fechas[$fechaOrden]] += $totalOrden;
            $cantidadPedidos++;
        }
        $reporte = [
            'ventas' => $ventas,
            'pedidos' => $cantidadPedidos,
            'promedio' => $cantidadPedidos > 0 ? $ventas / $cantidadPedidos : 0,
        ];

        require __DIR__ . '/../../public/views/reportes.php';
    }

    private function obtenerPedidosConDetalles(): array
    {
        $ordenes = (new Orden())->obtenerTodos();
        $detallesPorOrden = [];
        foreach ((new DetalleOrden())->obtenerTodos() as $detalle) {
            $detallesPorOrden[$detalle['orden_id']][] = [
                'cantidad' => $detalle['cantidad'],
                'nombre' => $detalle['producto_nombre'],
            ];
        }

        foreach ($ordenes as &$orden) {
            $orden['items'] = $detallesPorOrden[$orden['id']] ?? [];
        }
        unset($orden);

        return $ordenes;
    }

    private function normalizarEstado($estado): string
    {
        return strtolower(str_replace(
            [' ', 'á', 'é', 'í', 'ó', 'ú'],
            ['_', 'a', 'e', 'i', 'o', 'u'],
            trim((string) $estado)
        ));
    }
}
