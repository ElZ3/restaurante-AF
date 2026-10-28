# Olivo Real Restaurant

Sistema de gestión para restaurante desarrollado con **PHP nativo + PDO (MariaDB)** bajo el patrón **MVC estricto**.

## Stack
- Lenguaje: PHP 8+
- Base de datos: MariaDB (PDO, sentencias preparadas)
- Arquitectura: MVC (Modelo-Vista-Controlador)
- Enrutador: `public/index.php` (Front Controller)
- Despliegue: SFTP vía VS Code (`.vscode/sftp.json`)

## Estructura de carpetas

```
Olivo Real Restaurant/
├── constitution.md          # Reglas y criterios del proyecto
├── README.md                # Este archivo
├── .gitignore               # Ignora credenciales, dependencias, IDE, SO
├── .vscode/
│   └── sftp.json            # Configuración SFTP (HestiaCP) - IGNORADO en Git
├── config/
│   └── database.php         # Configuración de conexión PDO a MariaDB
├── public/
│   ├── index.php            # Enrutador principal (Front Controller)
│   ├── css/
│   │   └── estilos.css      # Estilos del sistema (absorbidos de olivo-real/)
│   ├── img/
│   │   ├── logo.png
│   │   ├── login.jpg
│   │   └── menu/            # Imágenes de productos del menú
│   ├── js/
│   │   └── sistema.js       # JavaScript común (absorbido de olivo-real/)
│   └── views/               # Vistas PHP (absorbidas de olivo-real/)
│       ├── login.php        # Vista: formulario de inicio de sesión
│       ├── dashboard.php    # Vista: panel principal
│       ├── usuarios.php     # Vista: gestión de usuarios
│       ├── mesas.php        # Vista: gestión de mesas
│       ├── menu.php         # Vista: menú del restaurante
│       ├── pedidos.php      # Vista: gestión de pedidos
│       ├── cocina.php       # Vista: cocina / órdenes en preparación
│       ├── reportes.php     # Vista: reportes y estadísticas
│       └── cuentas.php      # Vista: cuentas y pagos
├── app/
│   ├── controllers/
│   │   ├── LoginController.php      # Autenticación (Auth de Olivo Real)
│   │   ├── UsuarioController.php    # CRUD usuarios
│   │   └── MesaController.php       # CRUD mesas
│   ├── core/
│   │   ├── Auth.php           # Sistema de sesión y roles (de olivo-real/)
│   │   ├── Menu.php           # Menú y permisos (de olivo-real/)
│   │   ├── Csrf.php           # Protección CSRF (de olivo-real/)
│   │   └── helpers.php        # Funciones helper (de olivo-real/)
│   └── models/
│       ├── Database.php       # Clase conexión PDO (Singleton)
│       ├── Rol.php            # Modelo: tabla roles
│       ├── Usuario.php        # Modelo: tabla usuarios
│       ├── Area.php           # Modelo: tabla areas
│       ├── Mesa.php           # Modelo: tabla mesas
│       ├── Categoria.php      # Modelo: tabla categorias
│       ├── Producto.php       # Modelo: tabla productos
│       ├── Orden.php          # Modelo: tabla ordenes
│       ├── DetalleOrden.php   # Modelo: tabla detalles_orden
│       └── Reserva.php        # Modelo: tabla reservas
└── DIAGNOSTICO.md             # Reporte de estado del proyecto
```

## Reglas clave (constitution.md)

1. **MVC estricto:** Todo el tráfico pasa por `public/index.php`. Las vistas se alojan en `public/views/` y los controladores en `app/controllers/`.
2. **Autenticación:** Se utiliza el sistema `Auth` de `app/core/Auth.php` con `session_start()`, `password_verify()` y regeneración de ID de sesión.
3. **Seguridad:** Las credenciales de SFTP (`.vscode/sftp.json`) y los archivos de entorno (`.env`) están ignorados en Git y **no** deben subirse al repositorio.

## Rutas principales

| Ruta | Método | Vista / Controlador |
|------|--------|---------------------|
| `/` | GET | `LoginController@index` (login) |
| `/login` | GET | `LoginController@index` |
| `/autenticar` | POST | `LoginController@autenticar` |
| `/logout` | GET | `LoginController@logout` |
| `/dashboard` | GET | `UsuarioController@index` |
| `/usuarios` | GET | `UsuarioController@index` |
| `/mesas` | GET | `MesaController@index` |
| `/menu` | GET | `public/views/menu.php` |
| `/pedidos` | GET | `public/views/pedidos.php` |
| `/cocina` | GET | `public/views/cocina.php` |
| `/reportes` | GET | `public/views/reportes.php` |
| `/cuentas` | GET | `public/views/cuentas.php` |

## Base de datos

Crear la base de datos `db_restaurante` en MariaDB con las 9 tablas:
`roles`, `usuarios`, `areas`, `mesas`, `categorias`, `productos`, `ordenes`, `detalles_orden`, `reservas`.

Configurar credenciales en `config/database.php`.
