<?php
require __DIR__ . '/app/bootstrap.php';

Auth::exigirRol(Menu::roles('cuentas'));

// BACKEND: los descuentos solo los permite el administrador; validarlo también en PHP.
render('cuentas/index', [
    'titulo' => 'Cuentas y pagos',
    'pagina' => 'cuentas',
    'datos'  => [
        'cuentas'   => Cuenta::abiertas(),
        'historial' => Cuenta::historial(),
        'cajero'    => Auth::usuario()['nombre'],
        'descuento' => Auth::tieneRol(['admin']),
    ],
]);
