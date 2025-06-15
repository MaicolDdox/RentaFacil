-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para rentafacil
CREATE DATABASE IF NOT EXISTS `rentafacil` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `rentafacil`;

-- Volcando estructura para tabla rentafacil.arrendatarios
CREATE TABLE IF NOT EXISTS `arrendatarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `id_propiedad` int DEFAULT NULL,
  `id_propietario` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `arrendatarios_ibfk_2` (`id_propiedad`),
  KEY `arrendatarios_ibfk_3` (`id_propietario`),
  CONSTRAINT `arrendatarios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `arrendatarios_ibfk_2` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedades` (`id`),
  CONSTRAINT `arrendatarios_ibfk_3` FOREIGN KEY (`id_propietario`) REFERENCES `propietarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.arrendatarios: ~2 rows (aproximadamente)
INSERT INTO `arrendatarios` (`id`, `id_usuario`, `id_propiedad`, `id_propietario`) VALUES
	(30, 66, 12, 7),
	(31, 67, 14, 7);

-- Volcando estructura para tabla rentafacil.conceptos_pago
CREATE TABLE IF NOT EXISTS `conceptos_pago` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_contrato` int NOT NULL,
  `periodo` varchar(7) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Formato: YYYY-MM (e.g., 2025-06)',
  `concepto` enum('arriendo','agua','luz','gas','internet','seguro','daños','mantenimiento') COLLATE utf8mb4_general_ci NOT NULL,
  `monto_por_pagar` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','Completado','Retrasado') COLLATE utf8mb4_general_ci DEFAULT 'Pendiente',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_contrato` (`id_contrato`),
  CONSTRAINT `conceptos_pago_ibfk_1` FOREIGN KEY (`id_contrato`) REFERENCES `contratos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.conceptos_pago: ~16 rows (aproximadamente)
INSERT INTO `conceptos_pago` (`id`, `id_contrato`, `periodo`, `concepto`, `monto_por_pagar`, `estado`, `created_at`, `updated_at`) VALUES
	(113, 42, '2025-06', 'arriendo', 1.00, 'Retrasado', '2025-06-13 16:40:39', '2025-06-13 17:01:44'),
	(114, 42, '2025-06', 'agua', 1.00, 'Retrasado', '2025-06-13 16:40:39', '2025-06-13 17:05:09'),
	(115, 42, '2025-06', 'luz', 1.00, 'Retrasado', '2025-06-13 16:40:39', '2025-06-13 16:54:40'),
	(116, 42, '2025-06', 'gas', 1.00, 'Retrasado', '2025-06-13 16:40:39', '2025-06-14 09:28:09'),
	(117, 42, '2025-06', 'internet', 11.00, 'Retrasado', '2025-06-13 16:40:39', '2025-06-14 09:28:12'),
	(118, 42, '2025-06', 'seguro', 1.00, 'Retrasado', '2025-06-13 16:40:39', '2025-06-13 16:54:42'),
	(119, 42, '2025-06', 'daños', 1.00, 'Retrasado', '2025-06-13 16:40:39', '2025-06-13 16:54:43'),
	(120, 42, '2025-06', 'mantenimiento', 1.00, 'Retrasado', '2025-06-13 16:40:39', '2025-06-13 16:54:44'),
	(121, 42, '2025-07', 'arriendo', 2.00, 'Pendiente', '2025-06-14 09:28:26', '2025-06-14 09:28:26'),
	(122, 42, '2025-07', 'agua', 2.00, 'Pendiente', '2025-06-14 09:28:26', '2025-06-14 09:28:26'),
	(123, 42, '2025-07', 'luz', 2.00, 'Pendiente', '2025-06-14 09:28:26', '2025-06-14 09:28:26'),
	(124, 42, '2025-07', 'gas', 2.00, 'Pendiente', '2025-06-14 09:28:26', '2025-06-14 09:28:26'),
	(125, 42, '2025-07', 'internet', 2.00, 'Pendiente', '2025-06-14 09:28:26', '2025-06-14 09:28:26'),
	(126, 42, '2025-07', 'seguro', 2.00, 'Pendiente', '2025-06-14 09:28:26', '2025-06-14 09:28:26'),
	(127, 42, '2025-07', 'daños', 2.00, 'Pendiente', '2025-06-14 09:28:26', '2025-06-14 09:28:26'),
	(128, 42, '2025-07', 'mantenimiento', 2.00, 'Pendiente', '2025-06-14 09:28:26', '2025-06-14 09:28:26');

-- Volcando estructura para tabla rentafacil.contratos
CREATE TABLE IF NOT EXISTS `contratos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_propiedad` int DEFAULT NULL,
  `id_arrendatario` int DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('Activo','Finalizado','Cancelado') COLLATE utf8mb4_general_ci DEFAULT 'Activo',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_propiedad` (`id_propiedad`),
  KEY `id_arrendatario` (`id_arrendatario`),
  CONSTRAINT `contratos_ibfk_1` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedades` (`id`),
  CONSTRAINT `contratos_ibfk_2` FOREIGN KEY (`id_arrendatario`) REFERENCES `arrendatarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.contratos: ~1 rows (aproximadamente)
INSERT INTO `contratos` (`id`, `id_propiedad`, `id_arrendatario`, `fecha_inicio`, `fecha_fin`, `estado`, `created_at`, `updated_at`) VALUES
	(42, 12, 30, '2025-06-13', '2025-06-25', 'Activo', '2025-06-13 16:09:11', '2025-06-13 16:09:11');

-- Volcando estructura para tabla rentafacil.contratos_enviados
CREATE TABLE IF NOT EXISTS `contratos_enviados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_arrendatario` int DEFAULT NULL,
  `id_propietario` int DEFAULT NULL,
  `id_propiedad` int DEFAULT NULL,
  `archivo_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('Pendiente','Revisado','Rechazado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pendiente',
  `id_contrato_asociado` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_arrendatario` (`id_arrendatario`),
  KEY `id_propietario` (`id_propietario`),
  KEY `id_propiedad` (`id_propiedad`),
  KEY `id_contrato_asociado` (`id_contrato_asociado`),
  CONSTRAINT `contratos_enviados_ibfk_1` FOREIGN KEY (`id_arrendatario`) REFERENCES `arrendatarios` (`id`),
  CONSTRAINT `contratos_enviados_ibfk_2` FOREIGN KEY (`id_propietario`) REFERENCES `propietarios` (`id`),
  CONSTRAINT `contratos_enviados_ibfk_3` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedades` (`id`),
  CONSTRAINT `contratos_enviados_ibfk_4` FOREIGN KEY (`id_contrato_asociado`) REFERENCES `contratos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.contratos_enviados: ~1 rows (aproximadamente)
INSERT INTO `contratos_enviados` (`id`, `id_arrendatario`, `id_propietario`, `id_propiedad`, `archivo_pdf`, `fecha_envio`, `estado`, `id_contrato_asociado`) VALUES
	(14, 30, 7, 12, '../../../public/assets/pdf/contratos/contrato_66_20250613210833.pdf', '2025-06-13 16:08:33', 'Revisado', 42);

-- Volcando estructura para tabla rentafacil.estado_pagos
CREATE TABLE IF NOT EXISTS `estado_pagos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_contrato` int NOT NULL,
  `id_arrendatario` int NOT NULL,
  `id_propiedad` int NOT NULL,
  `periodo` varchar(7) COLLATE utf8mb4_general_ci NOT NULL,
  `monto_esperado` decimal(10,2) NOT NULL,
  `monto_pagado` decimal(10,2) DEFAULT '0.00',
  `estado` enum('Pagado','Debe Pago','Parcial') COLLATE utf8mb4_general_ci DEFAULT 'Debe Pago',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_contrato_periodo` (`id_contrato`,`periodo`),
  KEY `id_contrato` (`id_contrato`),
  KEY `id_arrendatario` (`id_arrendatario`),
  KEY `id_propiedad` (`id_propiedad`),
  CONSTRAINT `estado_pagos_ibfk_1` FOREIGN KEY (`id_contrato`) REFERENCES `contratos` (`id`),
  CONSTRAINT `estado_pagos_ibfk_2` FOREIGN KEY (`id_arrendatario`) REFERENCES `arrendatarios` (`id`),
  CONSTRAINT `estado_pagos_ibfk_3` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedades` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.estado_pagos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla rentafacil.eventos
CREATE TABLE IF NOT EXISTS `eventos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `titulo` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `fecha_evento` date DEFAULT NULL,
  `tipo_evento` enum('Visita','Recordatorio','Otro') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.eventos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla rentafacil.imagenes_propiedad
CREATE TABLE IF NOT EXISTS `imagenes_propiedad` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_propiedad` int DEFAULT NULL,
  `url_imagen` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `orden` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_propiedad` (`id_propiedad`),
  CONSTRAINT `imagenes_propiedad_ibfk_1` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedades` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.imagenes_propiedad: ~4 rows (aproximadamente)
INSERT INTO `imagenes_propiedad` (`id`, `id_propiedad`, `url_imagen`, `descripcion`, `orden`) VALUES
	(14, 12, 'assets/img/propiedades/6848555b19526_1.png', 'Imagen 1', 1),
	(16, 14, 'assets/img/propiedades/68485ae743a50_2.jpeg', 'Imagen 1', 1),
	(18, 16, 'assets/img/propiedades/6848d179da1d1_3.jpeg', 'Imagen 1', 1),
	(19, 17, 'assets/img/propiedades/6848d273d1f3f_4.jpeg', 'Imagen 1', 1);

-- Volcando estructura para tabla rentafacil.pagos
CREATE TABLE IF NOT EXISTS `pagos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_contrato` int DEFAULT NULL,
  `fecha_pago` date DEFAULT NULL,
  `periodo` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `estado` enum('Pendiente','Pagado','Retrasado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_contrato` (`id_contrato`),
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_contrato`) REFERENCES `contratos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.pagos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla rentafacil.password_resets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `correo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`),
  KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.password_resets: ~0 rows (aproximadamente)

-- Volcando estructura para tabla rentafacil.postulaciones
CREATE TABLE IF NOT EXISTS `postulaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_propiedad` int DEFAULT NULL,
  `nombre_postulante` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `correo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `telefono_postulante` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_postulacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`),
  KEY `id_propiedad` (`id_propiedad`),
  CONSTRAINT `postulaciones_ibfk_1` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedades` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.postulaciones: ~4 rows (aproximadamente)
INSERT INTO `postulaciones` (`id`, `id_propiedad`, `nombre_postulante`, `correo`, `telefono_postulante`, `fecha_postulacion`) VALUES
	(34, 14, 'Jhon Sebastian', 'jhon@gmail.com', '3214567890', '2025-06-14 13:06:37'),
	(35, 14, 'Isabella Gasca', 'isa@gmail.com', '3108860569', '2025-06-15 16:23:32'),
	(36, 16, 'Un Random', 'random@gmail.com', '3108860987', '2025-06-15 16:23:45'),
	(37, 14, 'Daniela mi amor', 'danielaamormio@gmail.com', '3108860987', '2025-06-15 16:24:10');

-- Volcando estructura para tabla rentafacil.propiedades
CREATE TABLE IF NOT EXISTS `propiedades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_propietario` int DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` enum('Disponible','Ocupado','Inactivo') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `zona` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_propietario` (`id_propietario`),
  CONSTRAINT `propiedades_ibfk_1` FOREIGN KEY (`id_propietario`) REFERENCES `propietarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.propiedades: ~4 rows (aproximadamente)
INSERT INTO `propiedades` (`id`, `id_propietario`, `direccion`, `estado`, `precio`, `descripcion`, `zona`, `created_at`, `updated_at`) VALUES
	(12, 7, 'calle 45 Sur #45-79', 'Ocupado', 4000000.00, 'Casa grande colombiana', 'Tello', '2025-06-10 10:55:07', '2025-06-13 16:09:11'),
	(14, 7, 'La Septima Cr.9 #12-90 B', 'Disponible', 2000000.00, 'Casa central espaciosa pero no para una familia tan grande', 'Tello', '2025-06-10 11:18:47', '2025-06-12 14:10:56'),
	(16, 7, 'Calle 90-A #45-77', 'Disponible', 5000000.00, 'Casa grande y moderna', 'Tello', '2025-06-10 19:44:41', '2025-06-12 14:21:33'),
	(17, 7, 'Cr.8 #190-D', 'Disponible', 50000.00, 'Casa humilde en zona guerrillera', 'Tello', '2025-06-10 19:48:51', '2025-06-11 10:52:19');

-- Volcando estructura para tabla rentafacil.propietarios
CREATE TABLE IF NOT EXISTS `propietarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `propietarios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.propietarios: ~1 rows (aproximadamente)
INSERT INTO `propietarios` (`id`, `id_usuario`) VALUES
	(7, 60);

-- Volcando estructura para tabla rentafacil.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `correo` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `verification_code` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.usuarios: ~3 rows (aproximadamente)
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `telefono`, `contrasena`, `created_at`, `updated_at`, `verification_code`, `is_verified`) VALUES
	(60, 'Maicol Duvan', 'maicolduvangascarodas@gmail.com', '3214743715', '$2y$10$TbRUV6y6nXyRYoE990d.ZeiBgc9rzwOV1euwB6a8/QcLwFmu/YDoW', '2025-06-10 07:53:14', '2025-06-10 07:53:33', '224612', 1),
	(66, 'Daniela Bustos', 'dani@gmail.com', '3213017238', '$2y$10$LPKdjo0WpQ49XeetbyEfgufClAw0VJhlw.hKcyCukuSPLKJ1OSAvi', '2025-06-11 14:35:33', '2025-06-11 14:35:33', NULL, 1),
	(67, 'Joel Santiago', 'joel@gmail.com', '3108860500', '$2y$10$jdGMOupqUtuG9fH7mLWPLeptCHED1L4tZz3T41/RkLM7pPxvpy.ty', '2025-06-11 21:02:07', '2025-06-11 21:02:07', NULL, 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
