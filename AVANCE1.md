# Avance 1 — Olivo Real Restaurant

## 1. Resumen ejecutivo

**Olivo Real Restaurant** es una aplicación web de gestión para restaurante, implementada en PHP nativo con arquitectura Modelo-Vista-Controlador (MVC) y MariaDB mediante PDO.

| Dato | Valor |
|---|---|
| Base de datos | `olivo_real` |
| Dominio local | `olivoreal.af` |
| Punto de entrada web | `public/index.php` |
| Vistas | `public/views/` |
| Backend | PHP nativo, PDO y MariaDB |

El Front Controller centraliza la resolución de rutas. La aplicación incluye inicio de sesión, sesión con roles y vistas para los principales módulos operativos del restaurante.

## 2. Estado actual y funcionalidades

### Autenticación

- `GET /login` muestra el formulario de acceso; si ya existe un usuario autenticado, redirige a `/dashboard`.
- `POST /autenticar` consulta el usuario activo usando la columna `email` como identificador. El formulario puede enviar el valor en `usuario` o `email`.
- Las contraseñas se generan con `password_hash(..., PASSWORD_BCRYPT)` y se verifican con `password_verify()`.
- Un acceso correcto crea la sesión y redirige a `/dashboard`; si las credenciales no coinciden, redirige a `/login?error=1`.

### Sesiones y autorización por roles

- `Auth` gestiona la cookie de sesión `OLIVOSESS`, configura opciones `HttpOnly` y `SameSite=Lax`, y habilita `Secure` cuando la solicitud usa HTTPS.
- Al autenticar se regenera el ID de sesión y se guarda el identificador, nombre y rol normalizado del usuario.
- `Auth.php` centraliza la sesión, normalización de roles, comprobación de permisos y cierre de sesión. `Menu.php` define los roles asociados a cada módulo.
- El controlador del dashboard aplica actualmente `Auth::exigirRol()` con los permisos de dashboard definidos en `Menu`.
- `GET /logout` destruye la sesión y redirige a `/login`.

> **Alcance actual del control de acceso:** aunque `Auth` y `Menu` proporcionan los mecanismos de autorización, las comprobaciones de sesión/rol en `UsuarioController` y `MesaController` están comentadas. Las rutas que sirven vistas directamente, como `/pedidos`, tampoco pasan por una comprobación de rol en un controlador. No se debe interpretar la definición de permisos del menú como protección activa de todas las rutas.

### Enrutamiento y módulos

`public/index.php` actúa como Front Controller y resuelve las rutas canónicas:

| Ruta | Resolución actual |
|---|---|
| `GET /login` | `LoginController::index()` |
| `POST /autenticar` | `LoginController::autenticar()` |
| `GET /logout` | `LoginController::logout()` |
| `GET /dashboard` | `DashboardController::index()` y `public/views/dashboard.php` |
| `GET /usuarios` | `UsuarioController::index()` y la vista de usuarios |
| `GET /mesas` | `MesaController::index()` y la vista de mesas |
| `GET /pedidos` | Renderizado directo de `public/views/pedidos.php` |

También existen vistas directas para `/menu`, `/cocina`, `/reportes` y `/cuentas`. El controlador de mesas incluye rutas CRUD; algunas acciones de escritura están actualmente comentadas y redirigen al listado.

### Datos y persistencia

- `Database.php` crea una conexión PDO reutilizable mediante el patrón Singleton.
- El método `Database::query()` usa `PDO::prepare()` y `execute($params)` para ejecutar consultas parametrizadas.
- Los modelos `Usuario` y `Mesa` utilizan esta capa para consultar y modificar sus datos; el repositorio contiene modelos adicionales para otros módulos.
- En `Mesa::obtenerTodos()` se corrigió el ordenamiento: ahora usa `ORDER BY m.id ASC`, evitando ordenar por la columna inexistente `m.numero`.

## 3. Estructura MVC actual

```text
Olivo_Real_Restaurant/
├── .gitignore
├── AVANCE1.md
├── README.md
├── DIAGNOSTICO.md
├── actualizacionMiercoles.md
├── constitution.md
├── config/
│   └── database.php
├── app/
│   ├── controllers/
│   │   ├── DashboardController.php
│   │   ├── LoginController.php
│   │   ├── MesaController.php
│   │   └── UsuarioController.php
│   ├── core/
│   │   ├── Auth.php
│   │   ├── Csrf.php
│   │   ├── Menu.php
│   │   └── helpers.php
│   └── models/
│       ├── Area.php
│       ├── Categoria.php
│       ├── Database.php
│       ├── DetalleOrden.php
│       ├── Mesa.php
│       ├── Orden.php
│       ├── Producto.php
│       ├── Reserva.php
│       ├── Rol.php
│       └── Usuario.php
└── public/
    ├── index.php
    ├── .htaccess
    ├── css/
    │   └── estilos.css
    ├── img/
    │   ├── login.jpg
    │   ├── logo.png
    │   └── menu/
    │       └── imágenes de productos
    ├── js/
    │   └── sistema.js
    └── views/
        ├── cocina.php
        ├── cuentas.php
        ├── dashboard.php
        ├── login.php
        ├── menu.php
        ├── mesas.php
        ├── pedidos.php
        ├── reportes.php
        └── usuarios.php
```

## 4. Configuración de Git

El `.gitignore` solicitado ignora por defecto el contenido del repositorio y permite únicamente `AVANCE1.md` y el propio `.gitignore` como archivos no ignorados. Los directorios se mantienen navegables mediante la excepción `!*/`.

Esta regla afecta a archivos **no rastreados**; no deja de rastrear automáticamente archivos que Git ya tenga en el índice.
