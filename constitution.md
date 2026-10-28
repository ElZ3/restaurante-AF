# Reglas y Criterios del Proyecto - Olivo Real Restaurant

## 1. Arquitectura y Stack
* Patrón de diseño: MVC estricto (Model-View-Controller).
* Lenguaje: PHP nativo con PDO para la base de datos MariaDB.
* Flujo: Todo el tráfico pasa por `public/index.php`. Las vistas se alojan en `public/views/` y los controladores en `app/controllers/`.

## 2. Regla Temporal de Colaboración (Frontend/Backend)
* Las validaciones de sesión y bloqueos de rutas en los controladores y middleware deben programarse, pero permanecerán **COMENTADAS** temporalmente. Esto permitirá el renderizado directo de las vistas para agilizar el maquetado del frontend sin requerir conexión a la base de datos local.

## 3. Criterios de Evaluación a Cumplir
* **Infraestructura Cloud (25%):** Servidor Ubuntu + HestiaCP funcionando con dominios y credenciales seguras.
* **Configuración SFTP (20%):** Despliegue automático de cambios mediante VS Code.
* **Funcionalidad (35%):** Módulos operativos, lógica de negocio relacional, CRUD de mesas, pedidos y usuarios.
* **Buenas Prácticas (20%):** Código limpio, estructura MVC, uso de Git y seguridad en consultas SQL.