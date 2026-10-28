<?php
/**
 * Usuarios del sistema.
 * BACKEND: SELECT sobre la tabla usuarios. Nunca se envía password_hash al navegador.
 */
class Usuario
{
    public static function todos()
    {
        return [
          ['id' => 1, 'n' => 'Ana López',      'c' => 'ana@olivoreal.com',      'rol' => 'admin',  'estado' => 'activo',   'por' => 'Sistema',   'fecha' => '2026-09-01'],
          ['id' => 2, 'n' => 'Carlos Méndez',  'c' => 'carlos@olivoreal.com',   'rol' => 'mesero', 'estado' => 'activo',   'por' => 'Ana López', 'fecha' => '2026-09-03'],
          ['id' => 3, 'n' => 'Laura Gómez',    'c' => 'laura@olivoreal.com',    'rol' => 'mesero', 'estado' => 'activo',   'por' => 'Ana López', 'fecha' => '2026-09-03'],
          ['id' => 4, 'n' => 'José Ramírez',   'c' => 'cocina@olivoreal.com',   'rol' => 'cocina', 'estado' => 'activo',   'por' => 'Ana López', 'fecha' => '2026-09-05'],
          ['id' => 5, 'n' => 'Valeria Torres', 'c' => 'caja@olivoreal.com',     'rol' => 'cajero', 'estado' => 'activo',   'por' => 'Ana López', 'fecha' => '2026-09-05'],
          ['id' => 6, 'n' => 'Miguel Herrera', 'c' => 'miguel@olivoreal.com',   'rol' => 'cocina', 'estado' => 'inactivo', 'por' => 'Ana López', 'fecha' => '2026-09-10'],
        ];
    }

    public static function roles()
    {
        return Auth::ROLES;
    }

    // Matriz de permisos (referencia de la guía de diseño)
    public static function matrizPermisos()
    {
        return [
          ['Dashboard',        'Completo', 'Limitado', 'Limitado', 'Limitado'],
          ['Reservas y mesas', 'Sí',       'Sí',       'No',       'Consulta'],
          ['Pedidos',          'Sí',       'Sí',       'Consulta', 'Consulta'],
          ['Panel de cocina',  'Sí',       'No',       'Sí',       'No'],
          ['Menú',             'Sí',       'Consulta', 'Consulta', 'No'],
          ['Cobros',           'Sí',       'No',       'No',       'Sí'],
          ['Reportes',         'Sí',       'No',       'No',       'Parcial'],
          ['Usuarios y config.', 'Sí',     'No',       'No',       'No'],
        ];
    }

    /**
     * Valida correo y contraseña. Devuelve ['id', 'nombre', 'rol'] o null.
     * BACKEND: buscar el usuario por correo, comprobar que esté activo, password_verify() contra password_hash,
     * limitar intentos (tabla intentos_login) y responder siempre el mismo error ("Correo o contraseña incorrectos").
     * Mientras no haya base de datos, devuelve null: para entrar se usa el modo demo.
     */
    public static function autenticar($correo, $clave)
    {
        return null;
    }
}
