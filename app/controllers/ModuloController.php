<?php

require_once __DIR__ . '/../core/Menu.php';

class ModuloController
{
    public function menu()
    {
        Auth::exigirRol(Menu::roles('menu'));

        $productos = (new Producto())->obtenerTodos();
        $categorias = (new Categoria())->obtenerActivas();
        require __DIR__ . '/../../public/views/menu/index.php';
    }

    public function pedidos()
    {
        Auth::exigirRol(Menu::roles('pedidos'));

        $pedidos = $this->obtenerPedidosConDetalles();
        $productos = (new Producto())->obtenerTodos();
        $categorias = (new Categoria())->obtenerTodos();
        $mesas = (new Mesa())->obtenerDisponibles();
        require __DIR__ . '/../../public/views/pedidos/index.php';
    }

    public function cocina()
    {
        Auth::exigirRol(Menu::roles('cocina'));

        $pedidos = $this->obtenerPedidosConDetalles();
        $minutosDemora = (int) ((new Configuracion())->obtenerPorClave('tiempo_demora_minutos') ?? 15);
        require __DIR__ . '/../../public/views/cocina/index.php';
    }

    // Devuelve el estado actual de pedidos y productos en JSON para peticiones Fetch/AJAX.
    // Se usa para refrescar la información mostrando el esqueleto de carga.
    public function listar()
    {
        Auth::exigirRol(Menu::roles('pedidos'));

        try {
            $pedidos = $this->obtenerPedidosConDetalles();
            $productos = (new Producto())->obtenerTodos();
            respuestaJson(['success' => true, 'pedidos' => $pedidos, 'productos' => $productos]);
        } catch (\PDOException $e) {
            error_log('[Pedidos] listar: ' . $e->getMessage());
            respuestaJson(['success' => false, 'message' => 'No se pudieron cargar los pedidos.'], 500);
        }
    }

    public function reservas()
    {
        Auth::exigirRol(Menu::roles('reservas'));

        $reservas = (new Reserva())->obtenerTodos();
        $mesas = (new Mesa())->obtenerTodos();
        require __DIR__ . '/../../public/views/reservas/index.php';
    }

    public function configuracion()
    {
        Auth::exigirRol(Menu::roles('config'));

        $areas = (new Area())->obtenerTodos();
        $mesas = (new Mesa())->obtenerTodos();
        $configuracion = [];
        foreach ((new Configuracion())->obtenerTodos() as $item) {
            $configuracion[$item['clave']] = $item['valor'];
        }
        require __DIR__ . '/../../public/views/configuracion/index.php';
    }

    public function reportes()
    {
        Auth::exigirRol(Menu::roles('reportes'));

        $ordenes = (new Pedido())->obtenerTodos();
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
            // Venta = cuenta cobrada (pedidos.estado_cuenta = 'pagada').
            if (($orden['estado_cuenta'] ?? '') !== 'pagada') {
                continue;
            }
            $marcaTiempo = strtotime((string) ($orden['fecha'] ?? ''));
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

        require __DIR__ . '/../../public/views/reportes/index.php';
    }

    private function obtenerPedidosConDetalles(): array
    {
        $ordenes = (new Pedido())->obtenerTodos();
        $detallesPorOrden = [];
        foreach ((new DetallePedido())->obtenerTodos() as $detalle) {
            $detallesPorOrden[$detalle['pedido_id']][] = [
                'cantidad' => $detalle['cantidad'],
                'nombre' => $detalle['producto_nombre'],
                'precio_unitario' => $detalle['precio_unitario'],
                'observaciones' => $detalle['observaciones'] ?? '',
            ];
        }

        foreach ($ordenes as &$orden) {
            // ENUM de la base: 'cancelada'; las vistas usan 'cancelado'.
            $orden['estado'] = Pedido::estadoAVista((string) ($orden['estado'] ?? ''));
            $orden['items'] = $detallesPorOrden[$orden['id']] ?? [];
            $orden['fecha_hora'] = $orden['fecha'] ?? '';
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
