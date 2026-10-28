<?php
require __DIR__ . '/app/bootstrap.php';

Auth::exigirRol(Menu::roles('mesas'));

render('mesas/index', [
    'titulo'   => 'Mesas y áreas',
    'pagina'   => 'mesas',
    'areas'    => Mesa::porAreas(),
    'estados'  => Mesa::estados(),
    'acciones' => Mesa::acciones(),
    'conteo'   => Mesa::conteoPorEstado(),
]);
