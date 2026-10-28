<?php
require __DIR__ . '/app/bootstrap.php';

Auth::exigirRol(Menu::roles('pedidos'));

$productos = Producto::paraPedidos();

render('pedidos/index', [
    'titulo'     => 'Pedidos',
    'pagina'     => 'pedidos',
    'estados'    => Pedido::estados(),
    'mesas'      => Mesa::paraPedidos(),
    'categorias' => array_values(array_unique(array_column($productos, 'cat'))),
    'datos'      => ['pedidos' => Pedido::todos(), 'productos' => $productos, 'usuario' => Auth::usuario()['nombre']],
]);
