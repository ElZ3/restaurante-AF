<?php
require __DIR__ . '/app/bootstrap.php';

Auth::exigirRol(Menu::roles('reservas'));

$mesas = Reserva::mesas();

render('reservas/lista', [
    'titulo'      => 'Reservas',
    'pagina'      => 'reservas',
    'mesas'       => $mesas,
    'nombre_mesa' => array_column($mesas, 1, 0),
    'estados'     => Reserva::estados(),
    'reservas'    => Reserva::todas(),
]);
