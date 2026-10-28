CREATE DATABASE IF NOT EXISTS olivo_real
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE olivo_real;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS historial_pedido, pagos, detalle_pedido, pedidos, reservas,
  productos, categorias, mesas, areas, intentos_login, configuracion, usuarios;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------- USUARIOS Y SEGURIDAD ----------
CREATE TABLE usuarios (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre        VARCHAR(100) NOT NULL,
  correo        VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  rol           ENUM('admin','mesero','cocina','cajero') NOT NULL,
  estado        ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  creado_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  creado_por    INT UNSIGNED NULL,
  FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Para limitar intentos de acceso fallidos
CREATE TABLE intentos_login (
  id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  correo  VARCHAR(120) NOT NULL,
  ip      VARCHAR(45)  NOT NULL,
  fecha   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (correo, fecha), INDEX (ip, fecha)
) ENGINE=InnoDB;

CREATE TABLE configuracion (
  clave  VARCHAR(50) PRIMARY KEY,
  valor  VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- ---------- MESAS Y RESERVAS ----------
CREATE TABLE areas (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(60) NOT NULL,
  descripcion VARCHAR(150) NULL,
  estado      ENUM('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB;

CREATE TABLE mesas (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  area_id   INT UNSIGNED NOT NULL,
  numero    SMALLINT UNSIGNED NOT NULL UNIQUE,
  capacidad TINYINT UNSIGNED NOT NULL,
  estado    ENUM('disponible','reservada','ocupada','fuera_servicio') NOT NULL DEFAULT 'disponible',
  FOREIGN KEY (area_id) REFERENCES areas(id)
) ENGINE=InnoDB;

CREATE TABLE reservas (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente       VARCHAR(100) NOT NULL,
  contacto      VARCHAR(80)  NOT NULL,
  fecha         DATE NOT NULL,
  hora          TIME NOT NULL,
  personas      TINYINT UNSIGNED NOT NULL,
  mesa_id       INT UNSIGNED NULL,
  estado        ENUM('pendiente','confirmada','llego','atendida','cancelada') NOT NULL DEFAULT 'pendiente',
  observaciones VARCHAR(255) NULL,
  creado_por    INT UNSIGNED NULL,
  creado_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE SET NULL,
  FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  INDEX (fecha, hora), INDEX (mesa_id, fecha)
) ENGINE=InnoDB;

-- ---------- MENÚ ----------
CREATE TABLE categorias (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(60) NOT NULL,
  descripcion VARCHAR(150) NULL,
  estado      ENUM('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB;

CREATE TABLE productos (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT UNSIGNED NOT NULL,
  nombre       VARCHAR(100) NOT NULL,
  descripcion  VARCHAR(255) NULL,
  precio       DECIMAL(10,2) NOT NULL CHECK (precio >= 0),
  imagen       VARCHAR(255) NULL,
  disponible   TINYINT(1) NOT NULL DEFAULT 1,   -- baja lógica, no se borra
  FOREIGN KEY (categoria_id) REFERENCES categorias(id)
) ENGINE=InnoDB;

-- ---------- PEDIDOS ----------
CREATE TABLE pedidos (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mesa_id       INT UNSIGNED NOT NULL,
  usuario_id    INT UNSIGNED NOT NULL,          -- mesero responsable
  reserva_id    INT UNSIGNED NULL,
  fecha         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  estado        ENUM('borrador','pendiente','en_preparacion','lista','entregada','cancelado')
                NOT NULL DEFAULT 'borrador',
  estado_cuenta ENUM('abierta','pagada','anulada') NOT NULL DEFAULT 'abierta',
  total         DECIMAL(10,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (mesa_id) REFERENCES mesas(id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE SET NULL,
  INDEX (estado), INDEX (fecha)
) ENGINE=InnoDB;

CREATE TABLE detalle_pedido (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id      INT UNSIGNED NOT NULL,
  producto_id    INT UNSIGNED NOT NULL,
  cantidad       SMALLINT UNSIGNED NOT NULL CHECK (cantidad > 0),
  precio_unitario DECIMAL(10,2) NOT NULL,       -- precio congelado al momento del pedido
  observaciones  VARCHAR(255) NULL,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;

CREATE TABLE historial_pedido (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id  INT UNSIGNED NOT NULL,
  estado     VARCHAR(30) NOT NULL,
  usuario_id INT UNSIGNED NULL,
  fecha      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- PAGOS ----------
CREATE TABLE pagos (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id   INT UNSIGNED NOT NULL,
  usuario_id  INT UNSIGNED NOT NULL,            -- quien cobra
  monto       DECIMAL(10,2) NOT NULL CHECK (monto > 0),
  metodo      ENUM('efectivo','tarjeta','transferencia','otro') NOT NULL,
  fecha       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  referencia  VARCHAR(80) NULL,
  token_unico CHAR(32) NOT NULL UNIQUE,         -- evita doble cobro por doble clic/recarga
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  INDEX (fecha)
) ENGINE=InnoDB;

-- =====================================================
-- DATOS INICIALES
-- =====================================================
INSERT INTO configuracion (clave, valor) VALUES
 ('nombre_restaurante','Olivo Real'),
 ('eslogan','El arte de compartir una buena mesa.'),
 ('direccion',''),
 ('telefono','');

-- Usuario administrador inicial  ·  correo: admin@olivoreal.com  ·  clave: Admin123!
-- ¡CAMBIAR LA CONTRASEÑA después del primer ingreso!
INSERT INTO usuarios (nombre, correo, password_hash, rol) VALUES
 ('Administrador','admin@olivoreal.com',
  '$2y$10$fniClfR6y0VwKPQrMkiL7.iuv4LDNa6xjvUU6hNwI6FEVNANQ2AVC','admin');

INSERT INTO areas (nombre, descripcion) VALUES
 ('Salón principal','Zona interior'),
 ('Terraza','Zona al aire libre'),
 ('Área privada','Eventos y grupos');

INSERT INTO mesas (area_id, numero, capacidad) VALUES
 (1,1,2),(1,2,4),(1,3,4),(1,4,6),(1,5,2),(1,6,4),
 (2,7,4),(2,8,4),(2,9,2),(2,10,6),
 (3,11,8),(3,12,10);

INSERT INTO categorias (nombre) VALUES ('Entradas'),('Platos fuertes'),('Bebidas'),('Postres');

INSERT INTO productos (categoria_id, nombre, descripcion, precio) VALUES
 (1,'Ensalada César','Lechuga romana, crutones y parmesano',6.50),
 (2,'Salmón a la plancha','Con vegetales salteados',14.00),
 (2,'Pasta Alfredo','Salsa cremosa con parmesano',12.50),
 (2,'Filete de res','Corte de 250 g con guarnición',18.00),
 (3,'Limonada natural','Jarra individual',3.00),
 (3,'Coca Cola','Botella 355 ml',2.50),
 (4,'Tiramisú','Postre clásico italiano',5.50);