<?php
require __DIR__ . '/app/bootstrap.php';

Auth::exigirRol(Menu::roles('usuarios'));

render('usuarios/index', [
    'titulo' => 'Usuarios',
    'pagina' => 'usuarios',
    'roles'  => Usuario::roles(),
    'matriz' => Usuario::matrizPermisos(),
    'datos'  => ['usuarios' => Usuario::todos(), 'roles' => Usuario::roles(), 'actual' => Auth::usuario()['nombre']],
]);
