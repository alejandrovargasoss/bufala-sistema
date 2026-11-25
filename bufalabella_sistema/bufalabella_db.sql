-- Base de datos Dibufala S.A.S. (El Rosal, Cundinamarca)
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `bufalabella_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `bufalabella_db`;

DROP TABLE IF EXISTS `detalle_ventas`;
DROP TABLE IF EXISTS `ventas`;
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `clientes`;
DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','vendedor','cliente') DEFAULT 'cliente',
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Usuario administrador (clave: password)
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `estado`) VALUES
(1, 'Administrador Dibufala', 'admin@bufalabella.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'activo');

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Quesos Frescos y Hilados', 'Mozzarella, Burrata, Stracciatella, Bocconcini, Ovoline y Ciliegine 100% leche de búfala'),
(2, 'Quesos Madurados y Untables', 'Ricotta artesanal y queso campesino de búfala'),
(3, 'Yogures y Bebidas Lácteas', 'Yogur griego y bebidas fermentadas de leche de búfala'),
(4, 'Mantequillas y Cremas', 'Mantequilla artesanal y crema de leche de búfala');

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `documento` varchar(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `documento` (`documento`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `clientes` (`id`, `nombre`, `apellido`, `documento`, `telefono`, `email`, `direccion`, `estado`) VALUES
(1, 'Trattoria Bella Italia', 'S.A.S.', '900456789-1', '3104567890', 'compras@bellaitalia.com', 'Calle 93 # 13-24, Bogotá', 'activo'),
(2, 'Mercado Orgánico', 'Sabana', '901234567-8', '3209876543', 'pedidos@mercadosabana.co', 'Km 15 Autopista Medellín, El Rosal', 'activo'),
(3, 'Hotel Boutique', 'La Pradera', '830112233-4', '3157894561', 'alimentos@hotellapradera.com', 'Vereda El Rodeo, El Rosal, Cundinamarca', 'activo'),
(4, 'Pizzería Artesanal', 'El Rosal', '900778899-2', '3185712967', 'pedidos@pizzeriaelrosal.com', 'Parque Principal, El Rosal, Cundinamarca', 'activo');

CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `precio` decimal(12,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `marca` varchar(80) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `categoria_id`, `precio`, `stock`, `marca`, `estado`) VALUES
(1, 'Burrata de Búfala Vaso 250g', 'Mozzarella fresca rellena de cuajada y crema suave, 100% leche de búfala libre de pastoreo', 1, 24500.00, 18, 'Dibufala', 'activo'),
(2, 'Stracciatella de Búfala 500g', 'Hilos tiernos de mozzarella artesanal mezclados con crema fresca de búfala', 1, 34900.00, 14, 'Dibufala', 'activo'),
(3, 'Bocconcini de Búfala 250g', 'Perlas de mozzarella tamaño bocado, ideales para pizzas artesanales y pasta fresca', 1, 19800.00, 25, 'Dibufala', 'activo'),
(4, 'Ovoline de Búfala 300g', 'Mozzarella ovalada ideal para tajar con tomate, albahaca, sánduches y lasagnas', 1, 22500.00, 16, 'Dibufala', 'activo'),
(5, 'Ciliegine de Búfala 250g', 'Esferas tamaño cereza 100% vegetarianas con cuajo vegetal, ideales para ensaladas y pinchos', 1, 20500.00, 20, 'Dibufala', 'activo'),
(6, 'Mozzarella Fior di Latte Búfala 500g', 'Bloque hilado tradicional de textura suave y fundido perfecto, bajo en colesterol', 1, 32000.00, 15, 'Dibufala', 'activo'),
(7, 'Queso Ricotta de Búfala 300g', 'Queso fino semidulce elaborado con suero de búfala, ligero y alto en calcio', 2, 16500.00, 4, 'Dibufala', 'activo'),
(8, 'Queso Campesino de Búfala 500g', 'Queso fresco prensado de sabor lácteo limpio elaborado en El Rosal, Cundinamarca', 2, 21000.00, 20, 'Dibufala', 'activo'),
(9, 'Yogur Griego de Búfala Natural 500g', 'Extra cremoso, sin azúcares añadidos y alto contenido proteico natural', 3, 18500.00, 30, 'Dibufala', 'activo'),
(10, 'Mantequilla de Búfala Artesanal 250g', 'Mantequilla pura batida lentamente, color marfil natural y rica en Omega 3', 4, 17200.00, 3, 'Dibufala', 'activo');

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `estado` enum('pendiente','completada','cancelada') DEFAULT 'completada',
  `observacion` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cliente` (`id_cliente`),
  CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `detalle_ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unit` decimal(12,2) NOT NULL,
  `subtotal` decimal(14,2) GENERATED ALWAYS AS (`cantidad` * `precio_unit`) STORED,
  PRIMARY KEY (`id`),
  KEY `id_venta` (`id_venta`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ventas` (`id`, `id_cliente`, `fecha`, `total`, `estado`, `observacion`) VALUES
(1, 1, DATE_SUB(NOW(), INTERVAL 22 DAY), 410000.00, 'completada', '[PSE · Bancolombia] {Ruta: Ruta Bogotá Norte} Pedido semanal restaurante'),
(2, 2, DATE_SUB(NOW(), INTERVAL 9 DAY), 354500.00, 'completada', '[Tarjeta Visa] {Ruta: Planta El Rosal} Surtido punto de venta'),
(3, 3, DATE_SUB(NOW(), INTERVAL 2 DAY), 287600.00, 'completada', '[Nequi / Daviplata] {Ruta: Ruta Subachoque-Facatativá} Desayunos gourmet');

INSERT INTO `detalle_ventas` (`id_venta`, `id_producto`, `cantidad`, `precio_unit`) VALUES
(1, 1, 10, 24500.00),
(1, 7, 10, 16500.00),
(2, 3, 10, 19800.00),
(2, 9, 5, 18500.00),
(2, 10, 3, 17200.00),
(3, 6, 5, 32000.00),
(3, 4, 4, 22500.00),
(3, 10, 2, 17200.00);

COMMIT;