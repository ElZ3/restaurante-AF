<?php
require __DIR__ . '/app/bootstrap.php';

Auth::exigirRol(Menu::roles('cocina'));

render('cocina/index', [
    'titulo'   => 'Cocina',
    'pagina'   => 'cocina',
    'ordenes'  => Pedido::paraCocina(),
    'columnas' => Pedido::columnasCocina(),
]);
