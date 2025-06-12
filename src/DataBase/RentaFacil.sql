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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.arrendatarios: ~2 rows (aproximadamente)

-- Volcando estructura para tabla rentafacil.contratos
CREATE TABLE IF NOT EXISTS `contratos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_propiedad` int DEFAULT NULL,
  `id_arrendatario` int DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `condiciones` text COLLATE utf8mb4_general_ci,
  `estado` enum('Activo','Finalizado','Cancelado') COLLATE utf8mb4_general_ci DEFAULT 'Activo',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_propiedad` (`id_propiedad`),
  KEY `id_arrendatario` (`id_arrendatario`),
  CONSTRAINT `contratos_ibfk_1` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedades` (`id`),
  CONSTRAINT `contratos_ibfk_2` FOREIGN KEY (`id_arrendatario`) REFERENCES `arrendatarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.contratos: ~2 rows (aproximadamente)

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.estado_pagos: ~5 rows (aproximadamente)

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.imagenes_propiedad: ~9 rows (aproximadamente)

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

-- Volcando datos para la tabla rentafacil.password_resets: ~2 rows (aproximadamente)

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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.postulaciones: ~0 rows (aproximadamente)

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.propiedades: ~7 rows (aproximadamente)

-- Volcando estructura para tabla rentafacil.propietarios
CREATE TABLE IF NOT EXISTS `propietarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `propietarios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.propietarios: ~4 rows (aproximadamente)

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
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla rentafacil.usuarios: ~8 rows (aproximadamente)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
