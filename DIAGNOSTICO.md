# DIAGNOSTICO.md - Royal_Olive_Restaurant

Reporte de estado del proyecto tras la migración definitiva y absorción de recursos de `olivo-real/`.

---

## 1. Estructura Adoptada

Se respeta la estructura de carpetas, formato y nombrado de archivos definida en `constitution.md`. No se renombró ni reestructuró ninguna vista ni estilo existente. El proyecto se renombró a **Royal_Olive_Restaurant**.

```
Royal_Olive_Restaurant/
├── constitution.md          # Reglas y criterios del proyecto (renombrado a Royal_Olive_Restaurant)
├── README.md                # Documentación del proyecto
├── .gitignore               # Ignora credenciales, dependencias, IDE, SO
├── .vscode/
│   └── sftp.json            # Configuración SFTP (HestiaCP) - IGNORADO en Git
├── config/
│   └── database.php         # Configuración de conexión PDO a MariaDB
├── public/
│   ├── index.php            # Enrutador principal (Front Controller)
│   ├── css/
│   │   └── estilos.css      # Estilos absorbidos de olivo-real/css/
│   ├── img/
│   │   ├── logo.png
│   │   ├── login.jpg
│   │   └── menu/            # 64 imágenes de productos absorbidas de olivo-real/img/
│   └── js/
│       └── sistema.js       # JavaScript absorbido de olivo-real/js/
├── app/
│   ├── controllers/
│   │   ├── LoginController.php      # Autenticación (sesión comentada)
│   │   ├── UsuarioController.php    # CRUD usuarios (sesión comentada)
│   │   └── MesaController.php       # CRUD mesas (sesión comentada)
│   └── models/
│       ├── Database.php             # Clase conexión PDO (Singleton)
│       ├── Rol.php                  # Modelo: tabla roles
│       ├── Usuario.php              # Modelo: tabla usuarios
│       ├── Area.php                 # Modelo: tabla areas
│       ├── Mesa.php                 # Modelo: tabla mesas
│       ├── Categoria.php            # Modelo: tabla categorias
│       ├── Producto.php             # Modelo: tabla productos
│       ├── Orden.php                # Modelo: tabla ordenes
│       ├── DetalleOrden.php         # Modelo: tabla detalles_orden
│       └── Reserva.php              # Modelo: tabla reservas
├── public/
│   ├── index.php            # Enrutador principal (Front Controller)
│   ├── css/
│   │   └── estilos.css      # Estilos absorbidos de olivo-real/css/
│   ├── img/
│   │   ├── logo.png
│   │   ├── login.jpg
│   │   └── menu/            # 64 imágenes de productos absorbidas de olivo-real/img/
│   ├── js/
│   │   └── sistema.js       # JavaScript absorbido de olivo-real/js/
│   └── views/               # Vistas PHP (movidas aquí para ubicación del compañero)
│       ├── login.php        # Vista: formulario de inicio de sesión
│       ├── dashboard.php    # Vista: panel principal
│       ├── usuarios.php     # Vista: gestión de usuarios
│       ├── mesas.php        # Vista: gestión de mesas
│       ├── menu.php         # Vista: menú del restaurante (absorbida)
│       ├── pedidos.php      # Vista: gestión de pedidos (absorbida)
│       ├── cocina.php       # Vista: cocina / órdenes (absorbida)
│       ├── reportes.php     # Vista: reportes (absorbida)
│       └── cuentas.php      # Vista: cuentas y pagos (absorbida)
└── DIAGNOSTICO.md           # Este archivo
```

**Nota:** La carpeta `olivo-real/` fue eliminada por completo tras la integración de sus recursos estáticos y vistas.

**Convenciones adoptadas:**
- Patrón MVC estricto: todo el tráfico pasa por `public/index.php`.
- Controladores en `app/controllers/`, vistas en `app/views/`, modelos en `app/models/`.
- Conexión PDO a MariaDB mediante `Database::getInstance()->getConnection()`.
- Sentencias preparadas con `?` placeholders (prevención de inyección SQL).
- Nombres de clase en PascalCase, archivos en el mismo nombre que la clase.
- Rutas de recursos estáticos apuntan a `public/` (`/css/`, `/img/`, `/js/`).

---

## 2. Funcionalidades y Vistas Operativas

Lo siguiente ya renderiza correctamente (renderizado directo, sin requerir conexión a BD):

| Vista | Ruta | Controlador asociado | Estado |
|-------|------|---------------------|--------|
| `login.php` | `/`, `/login` | `LoginController@index` | ✅ Renderiza directamente |
| `dashboard.php` | `/dashboard` | `UsuarioController@index` (placeholder) | ✅ Renderiza directamente |
| `usuarios.php` | `/usuarios` | `UsuarioController@index` | ✅ Renderiza directamente |
| `mesas.php` | `/mesas` | `MesaController@index` | ✅ Renderiza directamente |
| `menu.php` | `/menu` | Vista directa (sin controlador) | ✅ Renderiza directamente |
| `pedidos.php` | `/pedidos` | Vista directa (sin controlador) | ✅ Renderiza directamente |
| `cocina.php` | `/cocina` | Vista directa (sin controlador) | ✅ Renderiza directamente |
| `reportes.php` | `/reportes` | Vista directa (sin controlador) | ✅ Renderiza directamente |
| `cuentas.php` | `/cuentas` | Vista directa (sin controlador) | ✅ Renderiza directamente |

**Endpoints de acción (redirección, sin lógica BD activa):**

| Endpoint | Método | Controlador | Acción | Estado |
|----------|--------|-------------|--------|--------|
| `/autenticar` | POST | `LoginController` | `autenticar` | ✅ Redirige a `/login` (lógica comentada) |
| `/logout` | GET | `LoginController` | `logout` | ✅ Redirige a `/login` (sesión comentada) |
| `/usuarios/nuevo` | GET | `UsuarioController` | `nuevo` | ✅ Renderiza `usuarios.php` |
| `/usuarios/crear` | POST | `UsuarioController` | `crear` | ✅ Redirige a `/usuarios` (lógica comentada) |
| `/usuarios/editar/:id` | GET | `UsuarioController` | `editar` | ✅ Renderiza `usuarios.php` |
| `/usuarios/actualizar/:id` | POST | `UsuarioController` | `actualizar` | ✅ Redirige a `/usuarios` (lógica comentada) |
| `/usuarios/eliminar/:id` | GET | `UsuarioController` | `eliminar` | ✅ Redirige a `/usuarios` (lógica comentada) |
| `/mesas/nueva` | GET | `MesaController` | `nuevo` | ✅ Renderiza `mesas.php` |
| `/mesas/crear` | POST | `MesaController` | `crear` | ✅ Redirige a `/mesas` (lógica comentada) |
| `/mesas/editar/:id` | GET | `MesaController` | `editar` | ✅ Renderiza `mesas.php` |
| `/mesas/actualizar/:id` | POST | `MesaController` | `actualizar` | ✅ Redirige a `/mesas` (lógica comentada) |
| `/mesas/cambiar-estado/:id` | POST | `MesaController` | `cambiarEstado` | ✅ Redirige a `/mesas` (lógica comentada) |
| `/mesas/eliminar/:id` | GET | `MesaController` | `eliminar` | ✅ Redirige a `/mesas` (lógica comentada) |

**Recursos estáticos integrados:**
- `public/css/estilos.css` — estilos absorbidos de `olivo-real/css/`
- `public/js/sistema.js` — JavaScript absorbido de `olivo-real/js/`
- `public/img/logo.png`, `public/img/login.jpg`, `public/img/menu/` (64 imágenes) — absorbidos de `olivo-real/img/`

**Regla temporal de colaboración:** Todas las comprobaciones de sesión (`$_SESSION['usuario_id']`, `$_SESSION['rol']`) y bloqueos de rutas permanecen **COMENTADAS** en los controladores, permitiendo navegación libre por las vistas durante el maquetado frontend.

---

## 3. Pendientes y Puntos a Reforzar

### 3.1 Modelos construidos (listos para usar)

Todos los modelos para las 9 tablas de `db_restaurante` están creados en `app/models/`:

| Modelo | Tabla | Métodos implementados |
|--------|-------|----------------------|
| `Rol.php` | `roles` | obtenerTodos, obtenerPorId, crear, actualizar, eliminar |
| `Usuario.php` | `usuarios` | obtenerTodos, obtenerPorId, obtenerPorNombreUsuario, crear, actualizar, actualizarContrasena, eliminar, verificarContrasena |
| `Area.php` | `areas` | obtenerTodos, obtenerPorId, crear, actualizar, eliminar |
| `Mesa.php` | `mesas` | obtenerTodos, obtenerPorId, obtenerPorNumero, crear, actualizar, actualizarEstado, eliminar, obtenerDisponibles |
| `Categoria.php` | `categorias` | obtenerTodos, obtenerPorId, crear, actualizar, eliminar |
| `Producto.php` | `productos` | obtenerTodos, obtenerPorId, crear, actualizar, eliminar, obtenerDisponibles |
| `Orden.php` | `ordenes` | obtenerTodos, obtenerPorId, crear, actualizarEstado, eliminar, obtenerPorMesa, obtenerPorEstado |
| `DetalleOrden.php` | `detalles_orden` | obtenerTodos, obtenerPorId, obtenerPorOrden, crear, actualizar, eliminar, eliminarPorOrden |
| `Reserva.php` | `reservas` | obtenerTodos, obtenerPorId, crear, actualizarEstado, eliminar, obtenerPorFecha |

### 3.2 Controladores y endpoints faltantes por construir

Los siguientes módulos del sistema **no tienen controlador ni rutas** aún. Se deben crear siguiendo el mismo patrón de `MesaController`:

| Módulo | Tabla(s) | Controlador faltante | Endpoints SQL (PDO) necesarios |
|--------|----------|---------------------|--------------------------------|
| Categorías | `categorias` | `CategoriaController` | index, nuevo, crear, editar, actualizar, eliminar |
| Productos | `productos` | `ProductoController` | index, nuevo, crear, editar, actualizar, eliminar, obtenerDisponibles |
| Órdenes/Pedidos | `ordenes` | `OrdenController` | index, nuevo, crear, actualizarEstado, eliminar, obtenerPorMesa |
| Detalles de Orden | `detalles_orden` | `DetalleOrdenController` | obtenerPorOrden, crear, actualizar, eliminar |
| Reservas | `reservas` | `ReservaController` | index, nuevo, crear, actualizarEstado, eliminar, obtenerPorFecha |
| Áreas | `areas` | `AreaController` | index, nuevo, crear, editar, actualizar, eliminar |

### 3.3 Vistas faltantes por crear

Las vistas para los módulos faltantes aún no existen en `app/views/`:

- `categorias.php` (listado y formulario)
- `productos.php` (listado y formulario)
- `ordenes.php` o `pedidos_detalle.php` (listado de pedidos con detalle)
- `reservas.php` (listado y formulario de reservas)
- `areas.php` (listado y formulario de áreas)

### 3.4 Base de datos

Se requiere crear la base de datos `db_restaurante` en MariaDB con las 9 tablas y sus relaciones (claves foráneas). Luego actualizar `config/database.php` con las credenciales reales del entorno local o del servidor HestiaCP.

### 3.5 Seguridad

- ✅ `.vscode/sftp.json` está explícitamente ignorado en `.gitignore` (no se filtran credenciales al repositorio).
- ✅ `.env` y archivos de entorno están ignorados.
- ✅ Consultas SQL usan sentencias preparadas con PDO (placeholders `?`).
- ⚠️ Las contraseñas se hashean con `password_hash()` y se verifican con `password_verify()` (listo en `Usuario.php`, pendiente de descomentar en controladores).
- ⚠️ Las validaciones de sesión y bloqueo de rutas permanecen comentadas (regla temporal). Deben descomentarse antes de pasar a producción.

---

## Resumen

- **Estructura MVC:** Adoptada y respetada (sin renombrar vistas ni estilos del compañero).
- **Recursos estáticos:** Migrados de `olivo-real/` a `public/css/`, `public/img/`, `public/js/`.
- **Vistas absorbidas:** `menu.php`, `pedidos.php`, `cocina.php`, `reportes.php`, `cuentas.php` creadas en `app/views/` usando rutas a `public/`.
- **Carpeta `olivo-real/`:** Eliminada por completo.
- **Conexión PDO:** Integrada mediante `Database.php` + 10 modelos para las 9 tablas de `db_restaurante`.
- **Controladores operativos:** `LoginController`, `UsuarioController`, `MesaController` (con sesiones comentadas).
- **Enrutador:** `public/index.php` soporta rutas estáticas y con parámetros (`:id`).
- **Pendiente:** Crear controladores y vistas para categorías, productos, órdenes, detalles de orden, reservas y áreas; descomentar lógica de sesión para producción.
