-- Inicialización completa de la base de datos POS
-- Compatible con MySQL y TiDB Cloud

CREATE DATABASE IF NOT EXISTS posventas2
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_bin;

USE posventas2;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS detalles_venta;
DROP TABLE IF EXISTS ventas;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS usuarios;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- TABLA: usuarios
-- =====================================================
CREATE TABLE usuarios (
    id INT NOT NULL AUTO_INCREMENT,
    usuario VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL DEFAULT 'cajero',
    estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uk_usuarios_usuario (usuario)
);

-- La contraseña se conserva tal como estaba en la base original.
INSERT INTO usuarios (id, usuario, password_hash, rol, estado) VALUES
(1, 'administrador', '123456', 'administrador', 1);

-- =====================================================
-- TABLA: clientes
-- =====================================================
CREATE TABLE clientes (
    id INT NOT NULL AUTO_INCREMENT,
    cedula VARCHAR(20) NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    correo VARCHAR(100) DEFAULT NULL,
    fecha_registro TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_clientes_cedula (cedula)
);

INSERT INTO clientes
(id, cedula, nombre_completo, correo, fecha_registro) VALUES
(1, '1700000001', 'Juan Perez', 'juan.perez@espe.edu.ec', '2026-07-03 00:10:24'),
(2, '1700000002', 'Maria Gomez', 'maria.gomez@espe.edu.ec', '2026-07-03 00:10:24'),
(3, '1700000003', 'Carlos Ruiz', 'carlos.ruiz@espe.edu.ec', '2026-07-03 00:10:24'),
(4, '1700000004', 'Ana Silva', 'ana.silva@espe.edu.ec', '2026-07-03 00:10:24'),
(5, '1700000005', 'Luis Torres', 'luis.torres@espe.edu.ec', '2026-07-03 00:10:24'),
(6, '9999999999', 'Consumidor Final', NULL, '2026-07-17 00:23:50');

-- =====================================================
-- TABLA: productos
-- =====================================================
CREATE TABLE productos (
    id INT NOT NULL AUTO_INCREMENT,
    codigo_barras VARCHAR(50) NOT NULL,
    nombre_producto VARCHAR(100) NOT NULL,
    precio_actual DECIMAL(10,2) NOT NULL,
    stock_disponible INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uk_productos_codigo_barras (codigo_barras)
);

INSERT INTO productos
(id, codigo_barras, nombre_producto, precio_actual, stock_disponible) VALUES
(1, 'PROD-001', 'Laptop Dell XPS 13', 1200.00, 0),
(2, 'PROD-002', 'Mouse Inalámbrico Logitech', 25.50, 44),
(3, 'PROD-003', 'Teclado Mecánico Redragon', 45.00, 27),
(4, 'PROD-004', 'Monitor LG 27 pulgadas', 250.00, 18),
(5, 'PROD-005', 'Disco Duro SSD 1TB Kingston', 85.00, 40),
(6, 'PROD-006', 'Memoria RAM 16GB DDR5', 65.00, 35),
(7, 'PROD-007', 'Tarjeta Gráfica RTX 4060', 320.50, 10),
(8, 'PROD-008', 'Audífonos HyperX Cloud', 75.00, 25);

-- =====================================================
-- TABLA: ventas
-- =====================================================
CREATE TABLE ventas (
    id INT NOT NULL AUTO_INCREMENT,
    cliente_id INT NOT NULL,
    usuario_id INT NOT NULL,
    total_factura DECIMAL(10,2) NOT NULL,
    estado ENUM('Pagada', 'Anulada') NOT NULL DEFAULT 'Pagada',
    fecha_emision TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ventas_cliente_id (cliente_id),
    KEY idx_ventas_usuario_id (usuario_id),
    CONSTRAINT fk_ventas_clientes
        FOREIGN KEY (cliente_id)
        REFERENCES clientes (id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_ventas_usuarios
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id)
        ON DELETE RESTRICT
);

INSERT INTO ventas
(id, cliente_id, usuario_id, total_factura, estado, fecha_emision) VALUES
(1, 1, 1, 1225.50, 'Pagada', '2026-07-03 00:10:33'),
(2, 2, 1, 295.00, 'Pagada', '2026-07-03 00:10:33'),
(3, 3, 1, 130.00, 'Pagada', '2026-07-03 00:10:33'),
(4, 4, 1, 405.50, 'Pagada', '2026-07-03 00:10:33'),
(5, 6, 1, 87.98, 'Pagada', '2026-07-17 00:28:01'),
(6, 6, 1, 2760.00, 'Pagada', '2026-07-17 00:31:06'),
(7, 6, 1, 575.00, 'Pagada', '2026-07-17 00:49:05'),
(8, 6, 1, 4140.00, 'Anulada', '2026-07-17 00:49:27'),
(9, 6, 1, 213.90, 'Pagada', '2026-07-22 00:20:32'),
(10, 6, 1, 6900.00, 'Pagada', '2026-07-22 00:32:33'),
(11, 6, 1, 11040.00, 'Anulada', '2026-07-22 01:07:24'),
(12, 1, 1, 11040.00, 'Pagada', '2026-07-22 01:08:02'),
(13, 6, 1, 29.33, 'Pagada', '2026-07-23 19:56:16');

-- =====================================================
-- TABLA: detalles_venta
-- =====================================================
CREATE TABLE detalles_venta (
    id INT NOT NULL AUTO_INCREMENT,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_congelado DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_detalles_venta_id (venta_id),
    KEY idx_detalles_producto_id (producto_id),
    CONSTRAINT fk_detalles_ventas
        FOREIGN KEY (venta_id)
        REFERENCES ventas (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_detalles_productos
        FOREIGN KEY (producto_id)
        REFERENCES productos (id)
        ON DELETE RESTRICT
);

INSERT INTO detalles_venta
(id, venta_id, producto_id, cantidad, precio_congelado) VALUES
(1, 1, 1, 1, 1200.00),
(2, 1, 2, 1, 25.50),
(3, 2, 4, 1, 250.00),
(4, 2, 3, 1, 45.00),
(5, 3, 6, 2, 65.00),
(6, 4, 7, 1, 320.50),
(7, 4, 5, 1, 85.00),
(8, 5, 2, 3, 25.50),
(9, 6, 1, 2, 1200.00),
(10, 7, 4, 2, 250.00),
(11, 8, 1, 3, 1200.00),
(12, 9, 3, 3, 45.00),
(13, 9, 2, 2, 25.50),
(14, 10, 1, 5, 1200.00),
(15, 11, 1, 8, 1200.00),
(16, 12, 1, 8, 1200.00),
(17, 13, 2, 1, 25.50);

-- Ajustar los siguientes valores de AUTO_INCREMENT
ALTER TABLE usuarios AUTO_INCREMENT = 2;
ALTER TABLE clientes AUTO_INCREMENT = 7;
ALTER TABLE productos AUTO_INCREMENT = 9;
ALTER TABLE ventas AUTO_INCREMENT = 14;
ALTER TABLE detalles_venta AUTO_INCREMENT = 18;

-- Comprobación final
SELECT 'usuarios' AS tabla, COUNT(*) AS registros FROM usuarios
UNION ALL
SELECT 'clientes', COUNT(*) FROM clientes
UNION ALL
SELECT 'productos', COUNT(*) FROM productos
UNION ALL
SELECT 'ventas', COUNT(*) FROM ventas
UNION ALL
SELECT 'detalles_venta', COUNT(*) FROM detalles_venta;
