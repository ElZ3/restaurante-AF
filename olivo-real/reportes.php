<?php
require __DIR__ . '/app/bootstrap.php';

Auth::exigirRol(Menu::roles('reportes'));

render('reportes/index', [
    'titulo' => 'Reportes',
    'pagina' => 'reportes',
    'tipos'  => Reporte::tipos(Auth::usuario()['rol']),
    'datos'  => ['productos' => Reporte::productos(), 'dias' => Reporte::dias()],
]);
