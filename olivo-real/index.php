<?php
require __DIR__ . '/app/bootstrap.php';

redirigir(Auth::usuario() ? 'dashboard.php' : 'login.php');
