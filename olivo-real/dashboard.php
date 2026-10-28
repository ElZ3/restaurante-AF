<?php
require __DIR__ . '/app/bootstrap.php';

Auth::exigirRol(Menu::roles('dashboard'));

render('dashboard/index', [
    'titulo'         => 'Dashboard',
    'pagina'         => 'dashboard',
    'ventas_semana'  => Dashboard::ventasSemana(),
    'pedidos_estado' => Dashboard::pedidosPorEstado(),
    'reservas_hoy'   => Dashboard::reservasHoy(),
] + Dashboard::indicadores());
