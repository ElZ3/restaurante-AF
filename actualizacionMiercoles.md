# Reporte de Actualización e Integración - Miércoles

## 1. Cambios e Integraciones Aplicadas

- **Absorción de Estructura:** Se integró la carpeta `olivo-real/` en el proyecto principal, preservando el diseño CSS/JS, las plantillas HTML/PHP y la arquitectura MVC implementada por el compañero.
- **Assets Estáticos:** Se copiaron `public/css/estilos.css`, `public/js/sistema.js` y `public/img/` (incluyendo `logo.png`, `login.jpg` y 64 imágenes de menú) desde `olivo-real/`.
- **Core de Autenticación:** Se integró `app/core/` con `Auth.php`, `Menu.php`, `Csrf.php` y `helpers.php` desde `olivo-real/app/core/`, proporcionando manejo de sesión seguro, menú lateral con permisos por rol y protección CSRF.
- **Autoloader:** Se extendió el autoloader de `public/index.php` para buscar clases también en `app/core/`, manteniendo compatibilidad case-insensitive entre Windows y Linux.
- **Identidad de Marca:** Se renombró el sistema a **Olivo Real Restaurant** en todos los encabezados, marcas, títulos `<title>` y navegación (eliminando referencias a "Restaurante AF" y "Restaurate AF").
- **Vistas Actualizadas:** Todas las vistas en `public/views/` (`login.php`, `dashboard.php`, `usuarios.php`, `mesas.php`, `menu.php`, `pedidos.php`, `cocina.php`, `reportes.php`, `cuentas.php`) usan el nuevo branding y estilos de Olivo Real Restaurant.
- **Preservación de Entorno:** Se mantuvieron los archivos `.htaccess` y `.gitignore` con la configuración del servidor HestiaCP/Ubuntu (`olivoreal.af`).

## 2. Flujo de Autenticación y Acceso

1. **GET `/` o `/login`:** Presenta la pantalla de inicio de sesión adaptada con la marca **Olivo Real Restaurant**.
2. **POST `/autenticar`:** El controlador toma los campos `usuario` y `contrasena`, consulta el modelo mediante PDO, valida la clave mediante `password_verify()` y genera la sesión a través de `Auth::iniciarSesion()`.
3. **GET `/dashboard`:** Entrada al panel principal tras autenticación exitosa (protegido por sesión).
4. **GET `/logout`:** Cierre de sesión y destrucción de variables de sesión mediante `Auth::cerrar()`.

## 3. Matriz de Credenciales Habilitadas

| Rol | Usuario | Contraseña | Redirección |
| :--- | :--- | :--- | :--- |
| **SuperAdmin** | `admin` | `admin123` | `/dashboard` |
| **Gerente** | `gerente` | `admin123` | `/dashboard` |
| **Cajero** | `caja` | `admin123` | `/dashboard` |
| **Mesero** | `mesero` | `admin123` | `/dashboard` |
| **Cocina** | `cocina` | `admin123` | `/dashboard` |

## 4. Archivos Clave del Proyecto

| Archivo | Propósito |
|---------|-----------|
| `public/index.php` | Front Controller y enrutador principal con autoloader adaptable |
| `public/views/login.php` | Vista de inicio de sesión (acción `/autenticar`) |
| `app/controllers/LoginController.php` | Lógica de autenticación con `Auth::iniciarSesion()` / `Auth::cerrar()` |
| `app/core/Auth.php` | Sistema de sesión, roles y protección de rutas |
| `app/core/Menu.php` | Menú lateral y matriz de permisos por rol |
| `app/core/Csrf.php` | Protección contra formularios falsificados (CSRF) |
| `app/core/helpers.php` | Funciones `e()` y `redirigir()` |
| `config/database.php` | Conexión PDO a MariaDB (`olivo_real`, `root`, `AF123!`, `localhost`, `utf8mb4`) |
| `.vscode/sftp.json` | Despliegue SFTP a HestiaCP (host `192.168.1.150`, usuario `hestia`) |
| `.gitignore` | Evita subir credenciales (`.vscode/`, `.env`, `node_modules`, `*.md`) al repositorio |

## 5. Carpeta Eliminada

La carpeta `olivo-real/` fue eliminada por completo tras la integración de todos sus recursos en el proyecto principal.
