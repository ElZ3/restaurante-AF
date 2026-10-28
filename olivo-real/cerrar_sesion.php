<?php
require __DIR__ . '/app/bootstrap.php';

Auth::cerrar();
redirigir('login.php');
