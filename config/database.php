<?php
// Configuración de conexión a base de datos MariaDB (PDO)
// Pendiente de configurar credenciales reales del entorno local/servidor.

return [
    'host'     => 'localhost',
    'dbname'   => 'olivo_real',
    'username' => 'root',
    'password' => 'AF123!',
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
