<?php
require __DIR__ . '/app/bootstrap.php';

Auth::exigirRol(Menu::roles('menu'));

// BACKEND: validar este permiso también en PHP antes de guardar. Mesero y cocina solo consultan.
$puede_editar = Auth::tieneRol(['admin']);

render('menu/index', [
    'titulo'       => 'Menú',
    'pagina'       => 'menu',
    'categorias'   => Producto::categorias(),
    'puede_editar' => $puede_editar,
    'datos'        => ['productos' => Producto::todos(), 'editar' => $puede_editar],
]);
