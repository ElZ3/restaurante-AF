<?php
require __DIR__ . '/app/bootstrap.php';

Auth::exigirRol(Menu::roles('config'));

render('configuracion/index', [
    'titulo' => 'Configuración',
    'pagina' => 'config',
    'datos'  => ['areas' => Configuracion::areas(), 'mesas' => Configuracion::mesas()],
]);
