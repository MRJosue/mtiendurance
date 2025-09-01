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

-- Volcando estructura para tabla mtiadmin_portal.archivos_pedido
CREATE TABLE IF NOT EXISTS `archivos_pedido` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned NOT NULL,
  `nombre_archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta_archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_archivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_carga` tinyint NOT NULL DEFAULT '1' COMMENT '1=general, 2=otro, 3=evidencia entrega',
  `flag_descarga` tinyint(1) NOT NULL DEFAULT '1',
  `usuario_id` bigint unsigned NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `archivos_pedido_pedido_id_foreign` (`pedido_id`),
  KEY `archivos_pedido_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `archivos_pedido_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE,
  CONSTRAINT `archivos_pedido_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.archivos_proyecto
CREATE TABLE IF NOT EXISTS `archivos_proyecto` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proyecto_id` bigint unsigned DEFAULT NULL,
  `pre_proyecto_id` bigint unsigned DEFAULT NULL,
  `nombre_archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta_archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_carga` tinyint unsigned NOT NULL DEFAULT '1' COMMENT 'Tipo de carga del archivo: valores del 1 al 9',
  `flag_descarga` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si el archivo ha sido descargado',
  `version` int unsigned NOT NULL DEFAULT '1',
  `fecha_subida` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `archivos_proyecto_proyecto_id_foreign` (`proyecto_id`),
  KEY `archivos_proyecto_usuario_id_foreign` (`usuario_id`),
  KEY `archivos_proyecto_pre_proyecto_id_foreign` (`pre_proyecto_id`),
  CONSTRAINT `archivos_proyecto_pre_proyecto_id_foreign` FOREIGN KEY (`pre_proyecto_id`) REFERENCES `pre_proyectos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `archivos_proyecto_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `archivos_proyecto_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.caracteristicas
CREATE TABLE IF NOT EXISTS `caracteristicas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `flag_seleccion_multiple` tinyint NOT NULL DEFAULT '1' COMMENT 'Flag seleccion multiple',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ind_activo` tinyint NOT NULL DEFAULT '1' COMMENT 'Define si el registro esta activo 1 = activo 0 = in activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.caracteristica_opcion
CREATE TABLE IF NOT EXISTS `caracteristica_opcion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `caracteristica_id` bigint unsigned NOT NULL,
  `opcion_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `caracteristica_opcion_caracteristica_id_foreign` (`caracteristica_id`),
  KEY `caracteristica_opcion_opcion_id_foreign` (`opcion_id`),
  CONSTRAINT `caracteristica_opcion_caracteristica_id_foreign` FOREIGN KEY (`caracteristica_id`) REFERENCES `caracteristicas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `caracteristica_opcion_opcion_id_foreign` FOREIGN KEY (`opcion_id`) REFERENCES `opciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `flag_tallas` tinyint NOT NULL DEFAULT '0',
  `ind_activo` tinyint NOT NULL DEFAULT '1' COMMENT 'Define si el registro esta activo 1 = activo 0 = in activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.categoria_caracteristica
CREATE TABLE IF NOT EXISTS `categoria_caracteristica` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `categoria_id` bigint unsigned NOT NULL,
  `caracteristica_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categoria_caracteristica_categoria_id_foreign` (`categoria_id`),
  KEY `categoria_caracteristica_caracteristica_id_foreign` (`caracteristica_id`),
  CONSTRAINT `categoria_caracteristica_caracteristica_id_foreign` FOREIGN KEY (`caracteristica_id`) REFERENCES `caracteristicas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `categoria_caracteristica_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.categoria_producto
CREATE TABLE IF NOT EXISTS `categoria_producto` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `categoria_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categoria_producto_categoria_id_foreign` (`categoria_id`),
  KEY `categoria_producto_producto_id_foreign` (`producto_id`),
  CONSTRAINT `categoria_producto_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `categoria_producto_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.chats
CREATE TABLE IF NOT EXISTS `chats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proyecto_id` bigint unsigned NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chats_proyecto_id_foreign` (`proyecto_id`),
  CONSTRAINT `chats_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.ciudades
CREATE TABLE IF NOT EXISTS `ciudades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `estado_id` bigint unsigned NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ciudades_estado_id_foreign` (`estado_id`),
  CONSTRAINT `ciudades_estado_id_foreign` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.ciudades_tipo_envio
CREATE TABLE IF NOT EXISTS `ciudades_tipo_envio` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ciudad_id` bigint unsigned NOT NULL,
  `tipo_envio_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ciudades_tipo_envio_ciudad_id_foreign` (`ciudad_id`),
  KEY `ciudades_tipo_envio_tipo_envio_id_foreign` (`tipo_envio_id`),
  CONSTRAINT `ciudades_tipo_envio_ciudad_id_foreign` FOREIGN KEY (`ciudad_id`) REFERENCES `ciudades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ciudades_tipo_envio_tipo_envio_id_foreign` FOREIGN KEY (`tipo_envio_id`) REFERENCES `tipo_envio` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.clientes
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint unsigned NOT NULL,
  `nombre_empresa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clientes_email_unique` (`email`),
  KEY `clientes_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `clientes_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.direcciones_entrega
CREATE TABLE IF NOT EXISTS `direcciones_entrega` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint unsigned NOT NULL,
  `nombre_contacto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `calle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ciudad_id` bigint unsigned NOT NULL,
  `estado_id` bigint unsigned NOT NULL,
  `pais_id` bigint unsigned NOT NULL,
  `codigo_postal` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flag_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `nombre_empresa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `direcciones_entrega_ciudad_id_foreign` (`ciudad_id`),
  KEY `direcciones_entrega_estado_id_foreign` (`estado_id`),
  KEY `direcciones_entrega_pais_id_foreign` (`pais_id`),
  KEY `direcciones_entrega_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `direcciones_entrega_ciudad_id_foreign` FOREIGN KEY (`ciudad_id`) REFERENCES `ciudades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `direcciones_entrega_estado_id_foreign` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `direcciones_entrega_pais_id_foreign` FOREIGN KEY (`pais_id`) REFERENCES `paises` (`id`) ON DELETE CASCADE,
  CONSTRAINT `direcciones_entrega_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.direcciones_fiscales
CREATE TABLE IF NOT EXISTS `direcciones_fiscales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint unsigned NOT NULL,
  `rfc` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `calle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ciudad_id` bigint unsigned NOT NULL,
  `estado_id` bigint unsigned NOT NULL,
  `pais_id` bigint unsigned NOT NULL,
  `codigo_postal` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `flag_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `direcciones_fiscales_ciudad_id_foreign` (`ciudad_id`),
  KEY `direcciones_fiscales_estado_id_foreign` (`estado_id`),
  KEY `direcciones_fiscales_pais_id_foreign` (`pais_id`),
  KEY `direcciones_fiscales_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `direcciones_fiscales_ciudad_id_foreign` FOREIGN KEY (`ciudad_id`) REFERENCES `ciudades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `direcciones_fiscales_estado_id_foreign` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `direcciones_fiscales_pais_id_foreign` FOREIGN KEY (`pais_id`) REFERENCES `paises` (`id`) ON DELETE CASCADE,
  CONSTRAINT `direcciones_fiscales_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.empresas
CREATE TABLE IF NOT EXISTS `empresas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rfc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.estados
CREATE TABLE IF NOT EXISTS `estados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pais_id` bigint unsigned NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estados_pais_id_foreign` (`pais_id`),
  CONSTRAINT `estados_pais_id_foreign` FOREIGN KEY (`pais_id`) REFERENCES `paises` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.filtros_produccion
CREATE TABLE IF NOT EXISTS `filtros_produccion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `orden` int DEFAULT NULL,
  `config` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filtros_produccion_slug_unique` (`slug`),
  KEY `filtros_produccion_created_by_foreign` (`created_by`),
  CONSTRAINT `filtros_produccion_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.filtro_produccion_caracteristicas
CREATE TABLE IF NOT EXISTS `filtro_produccion_caracteristicas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `filtro_produccion_id` bigint unsigned NOT NULL,
  `caracteristica_id` bigint unsigned NOT NULL,
  `orden` int DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `ancho` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `render` enum('texto','badges','chips','iconos','count') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'texto',
  `multivalor_modo` enum('inline','badges','count') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inline',
  `max_items` tinyint unsigned NOT NULL DEFAULT '4',
  `fallback` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_filtro_caracteristica` (`filtro_produccion_id`,`caracteristica_id`),
  KEY `filtro_produccion_caracteristicas_caracteristica_id_foreign` (`caracteristica_id`),
  CONSTRAINT `filtro_produccion_caracteristicas_caracteristica_id_foreign` FOREIGN KEY (`caracteristica_id`) REFERENCES `caracteristicas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `filtro_produccion_caracteristicas_filtro_produccion_id_foreign` FOREIGN KEY (`filtro_produccion_id`) REFERENCES `filtros_produccion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.filtro_produccion_productos
CREATE TABLE IF NOT EXISTS `filtro_produccion_productos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `filtro_produccion_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_filtro_producto` (`filtro_produccion_id`,`producto_id`),
  KEY `filtro_produccion_productos_producto_id_foreign` (`producto_id`),
  CONSTRAINT `filtro_produccion_productos_filtro_produccion_id_foreign` FOREIGN KEY (`filtro_produccion_id`) REFERENCES `filtros_produccion` (`id`) ON DELETE CASCADE,
  CONSTRAINT `filtro_produccion_productos_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.flujos_produccion
CREATE TABLE IF NOT EXISTS `flujos_produccion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `config` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.grupos_orden
CREATE TABLE IF NOT EXISTS `grupos_orden` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grupos_orden_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.grupos_tallas
CREATE TABLE IF NOT EXISTS `grupos_tallas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre del grupo de tallas, por ejemplo, Hombre, Mujer, Niños',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ind_activo` tinyint NOT NULL DEFAULT '1' COMMENT 'Define si el registro esta activo 1 = activo 0 = in activo',
  PRIMARY KEY (`id`),
  UNIQUE KEY `grupos_tallas_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.grupo_orden_permission
CREATE TABLE IF NOT EXISTS `grupo_orden_permission` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `grupo_orden_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  `orden` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grupo_orden_permission_grupo_orden_id_permission_id_unique` (`grupo_orden_id`,`permission_id`),
  KEY `grupo_orden_permission_permission_id_foreign` (`permission_id`),
  CONSTRAINT `grupo_orden_permission_grupo_orden_id_foreign` FOREIGN KEY (`grupo_orden_id`) REFERENCES `grupos_orden` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grupo_orden_permission_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.grupo_tallas_detalle
CREATE TABLE IF NOT EXISTS `grupo_tallas_detalle` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `grupo_talla_id` bigint unsigned NOT NULL,
  `talla_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grupo_tallas_detalle_grupo_talla_id_foreign` (`grupo_talla_id`),
  KEY `grupo_tallas_detalle_talla_id_foreign` (`talla_id`),
  CONSTRAINT `grupo_tallas_detalle_grupo_talla_id_foreign` FOREIGN KEY (`grupo_talla_id`) REFERENCES `grupos_tallas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grupo_tallas_detalle_talla_id_foreign` FOREIGN KEY (`talla_id`) REFERENCES `tallas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.layouts
CREATE TABLE IF NOT EXISTS `layouts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `producto_id` bigint unsigned DEFAULT NULL,
  `categoria_id` bigint unsigned DEFAULT NULL,
  `usuario_id` bigint unsigned NOT NULL,
  `ind_activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `layouts_producto_id_foreign` (`producto_id`),
  KEY `layouts_categoria_id_foreign` (`categoria_id`),
  KEY `layouts_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `layouts_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `layouts_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `layouts_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.layout_elementos
CREATE TABLE IF NOT EXISTS `layout_elementos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `layout_id` bigint unsigned NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caracteristica_id` bigint unsigned DEFAULT NULL,
  `letra` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posicion_x` int NOT NULL DEFAULT '0',
  `posicion_y` int NOT NULL DEFAULT '0',
  `ancho` int NOT NULL DEFAULT '100',
  `alto` int NOT NULL DEFAULT '100',
  `orden` int NOT NULL DEFAULT '0',
  `configuracion` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `layout_elementos_layout_id_foreign` (`layout_id`),
  KEY `layout_elementos_caracteristica_id_foreign` (`caracteristica_id`),
  CONSTRAINT `layout_elementos_caracteristica_id_foreign` FOREIGN KEY (`caracteristica_id`) REFERENCES `caracteristicas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `layout_elementos_layout_id_foreign` FOREIGN KEY (`layout_id`) REFERENCES `layouts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.mensajes_chat
CREATE TABLE IF NOT EXISTS `mensajes_chat` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `chat_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` tinyint NOT NULL DEFAULT '1' COMMENT '1 entrada de chat  2 evento',
  `fecha_envio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mensajes_chat_chat_id_foreign` (`chat_id`),
  KEY `mensajes_chat_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `mensajes_chat_chat_id_foreign` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mensajes_chat_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1126 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.model_has_permissions
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.model_has_roles
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.opciones
CREATE TABLE IF NOT EXISTS `opciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pasos` double(8,2) NOT NULL,
  `minutoPaso` time NOT NULL,
  `valoru` double(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ind_activo` tinyint NOT NULL DEFAULT '1' COMMENT 'Define si el registro esta activo 1 = activo 0 = in activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.ordenes_produccion
CREATE TABLE IF NOT EXISTS `ordenes_produccion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `flujo_id` int DEFAULT NULL,
  `create_user` bigint unsigned NOT NULL,
  `assigned_user_id` bigint unsigned DEFAULT NULL,
  `tipo` enum('CORTE','SUBLIMADO','COSTURA','MAQUILA','FACTURACION','ENVIO','OTRO','RECHAZADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CORTE',
  `estado` enum('SIN INICIAR','EN PROCESO','TERMINADO','CANCELADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SIN INICIAR',
  `flag_activo` tinyint NOT NULL DEFAULT '0' COMMENT '0 = Inactivo, 1 = Activo',
  `prioridad` tinyint unsigned NOT NULL DEFAULT '3' COMMENT '1: Alta, 2: Media, 3: Baja',
  `fecha_sin_iniciar` timestamp NULL DEFAULT NULL,
  `fecha_en_proceso` timestamp NULL DEFAULT NULL,
  `fecha_terminado` timestamp NULL DEFAULT NULL,
  `fecha_cancelado` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ordenes_produccion_create_user_foreign` (`create_user`),
  KEY `ordenes_produccion_assigned_user_id_foreign` (`assigned_user_id`),
  CONSTRAINT `ordenes_produccion_assigned_user_id_foreign` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordenes_produccion_create_user_foreign` FOREIGN KEY (`create_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.orden_corte
CREATE TABLE IF NOT EXISTS `orden_corte` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `orden_produccion_id` bigint unsigned NOT NULL,
  `tallas` json NOT NULL,
  `tallas_entregadas` json DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `caracteristicas` json DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orden_corte_orden_produccion_id_foreign` (`orden_produccion_id`),
  CONSTRAINT `orden_corte_orden_produccion_id_foreign` FOREIGN KEY (`orden_produccion_id`) REFERENCES `ordenes_produccion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.orden_paso
CREATE TABLE IF NOT EXISTS `orden_paso` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `orden_produccion_id` bigint unsigned NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grupo_paralelo` int unsigned NOT NULL,
  `estado` enum('PENDIENTE','EN_PROCESO','COMPLETADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `fecha_inicio` timestamp NULL DEFAULT NULL,
  `fecha_fin` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orden_paso_orden_produccion_id_foreign` (`orden_produccion_id`),
  CONSTRAINT `orden_paso_orden_produccion_id_foreign` FOREIGN KEY (`orden_produccion_id`) REFERENCES `ordenes_produccion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.paises
CREATE TABLE IF NOT EXISTS `paises` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.pedido
CREATE TABLE IF NOT EXISTS `pedido` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proyecto_id` bigint unsigned DEFAULT NULL,
  `producto_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `cliente_id` bigint unsigned DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total` decimal(10,2) NOT NULL,
  `total_minutos` double(8,2) DEFAULT NULL COMMENT 'Guarda la suma total de tiempo tomado de las opciones',
  `total_pasos` int DEFAULT NULL COMMENT 'Guarda el total de Operaciones / pasos',
  `resumen_tiempos` json DEFAULT NULL COMMENT 'Resumen JSON con detalle de pasos, tiempos y opciones',
  `estatus` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion_pedido` text COLLATE utf8mb4_unicode_ci,
  `instrucciones_muestra` text COLLATE utf8mb4_unicode_ci,
  `flag_facturacion` tinyint NOT NULL DEFAULT '1' COMMENT '0: no se hace factura; 1: se hace factura',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `direccion_fiscal_id` bigint unsigned DEFAULT NULL,
  `direccion_fiscal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion_entrega_id` bigint unsigned DEFAULT NULL,
  `direccion_entrega` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_tipo_envio` bigint unsigned DEFAULT NULL,
  `tipo` enum('POR DEFINIR','PEDIDO','MUESTRA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'POR DEFINIR',
  `estatus_entrega_muestra` enum('PENDIENTE','DIGITAL','FISICA') COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `estatus_muestra` enum('PENDIENTE','SOLICITADA','MUESTRA LISTA','ENTREGADA','COMPLETADA','CANCELADA') COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `estado` enum('POR APROBAR','APROBADO','ENTREGADO','RECHAZADO','ARCHIVADO','POR REPROGRAMAR') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'POR APROBAR',
  `estado_produccion` enum('POR APROBAR','POR PROGRAMAR','PROGRAMADO','IMPRESIÓN','CORTE','COSTURA','ENTREGA','FACTURACIÓN','COMPLETADO','RECHAZADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'POR APROBAR',
  `flag_aprobar_sin_fechas` tinyint NOT NULL DEFAULT '0' COMMENT '1: permite aprobar el pedido sin fechas, 0: requiere fechas',
  `flag_solicitud_aprobar_sin_fechas` tinyint NOT NULL DEFAULT '0' COMMENT '1: permite aprobar el pedido sin fechas, 0: requiere fechas',
  `fecha_produccion` date DEFAULT NULL,
  `fecha_embarque` date DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_uploaded_file_id` bigint unsigned DEFAULT NULL COMMENT 'Referencia al último archivo cargado en el proyecto',
  PRIMARY KEY (`id`),
  KEY `pedido_user_id_foreign` (`user_id`),
  KEY `pedido_proyecto_id_foreign` (`proyecto_id`),
  KEY `pedido_producto_id_foreign` (`producto_id`),
  CONSTRAINT `pedido_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pedido_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pedido_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.pedido_caracteristicas
CREATE TABLE IF NOT EXISTS `pedido_caracteristicas` (
  `pedido_id` bigint unsigned NOT NULL,
  `caracteristica_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`pedido_id`,`caracteristica_id`),
  KEY `pedido_caracteristicas_caracteristica_id_foreign` (`caracteristica_id`),
  CONSTRAINT `pedido_caracteristicas_caracteristica_id_foreign` FOREIGN KEY (`caracteristica_id`) REFERENCES `caracteristicas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pedido_caracteristicas_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.pedido_estados
CREATE TABLE IF NOT EXISTS `pedido_estados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned NOT NULL,
  `proyecto_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned NOT NULL,
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `fecha_inicio` timestamp NULL DEFAULT NULL,
  `fecha_fin` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_estados_pedido_id_foreign` (`pedido_id`),
  KEY `pedido_estados_proyecto_id_foreign` (`proyecto_id`),
  KEY `pedido_estados_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `pedido_estados_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pedido_estados_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pedido_estados_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.pedido_opciones
CREATE TABLE IF NOT EXISTS `pedido_opciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned NOT NULL,
  `opcion_id` bigint unsigned NOT NULL,
  `valor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_opciones_pedido_id_foreign` (`pedido_id`),
  KEY `pedido_opciones_opcion_id_foreign` (`opcion_id`),
  CONSTRAINT `pedido_opciones_opcion_id_foreign` FOREIGN KEY (`opcion_id`) REFERENCES `opciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pedido_opciones_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.pedido_orden_produccion
CREATE TABLE IF NOT EXISTS `pedido_orden_produccion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned NOT NULL,
  `orden_produccion_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_orden_produccion_pedido_id_foreign` (`pedido_id`),
  KEY `pedido_orden_produccion_orden_produccion_id_foreign` (`orden_produccion_id`),
  CONSTRAINT `pedido_orden_produccion_orden_produccion_id_foreign` FOREIGN KEY (`orden_produccion_id`) REFERENCES `ordenes_produccion` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pedido_orden_produccion_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.pedido_tallas
CREATE TABLE IF NOT EXISTS `pedido_tallas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned NOT NULL,
  `talla_id` bigint unsigned NOT NULL,
  `grupo_talla_id` bigint unsigned NOT NULL,
  `cantidad` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_tallas_grupo_talla_id_foreign` (`grupo_talla_id`),
  KEY `pedido_tallas_pedido_id_foreign` (`pedido_id`),
  KEY `pedido_tallas_talla_id_foreign` (`talla_id`),
  CONSTRAINT `pedido_tallas_grupo_talla_id_foreign` FOREIGN KEY (`grupo_talla_id`) REFERENCES `grupos_tallas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pedido_tallas_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pedido_tallas_talla_id_foreign` FOREIGN KEY (`talla_id`) REFERENCES `tallas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.pedido_tarea
CREATE TABLE IF NOT EXISTS `pedido_tarea` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned NOT NULL,
  `tarea_produccion_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_tarea_pedido_id_foreign` (`pedido_id`),
  KEY `pedido_tarea_tarea_produccion_id_foreign` (`tarea_produccion_id`),
  CONSTRAINT `pedido_tarea_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pedido_tarea_tarea_produccion_id_foreign` FOREIGN KEY (`tarea_produccion_id`) REFERENCES `tareas_produccion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.pre_proyectos
CREATE TABLE IF NOT EXISTS `pre_proyectos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint unsigned NOT NULL,
  `direccion_fiscal_id` bigint unsigned DEFAULT NULL,
  `direccion_fiscal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion_entrega_id` bigint unsigned DEFAULT NULL,
  `direccion_entrega` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `id_tipo_envio` bigint NOT NULL COMMENT 'Guarda la referencia del tipo de envio',
  `tipo` enum('PROYECTO','MUESTRA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PROYECTO',
  `numero_muestras` int NOT NULL DEFAULT '0',
  `estado` enum('PENDIENTE','RECHAZADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_produccion` date DEFAULT NULL,
  `fecha_embarque` date DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `categoria_sel` json DEFAULT NULL,
  `flag_armado` tinyint NOT NULL DEFAULT '1' COMMENT '1 = Activo (Los pedidos seran armados), 0 = Inactivo',
  `producto_sel` json DEFAULT NULL,
  `caracteristicas_sel` json DEFAULT NULL,
  `opciones_sel` json DEFAULT NULL,
  `total_piezas_sel` json DEFAULT NULL COMMENT 'Guarda el total de piezas',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pre_proyectos_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `pre_proyectos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.productos
CREATE TABLE IF NOT EXISTS `productos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `categoria_id` bigint unsigned DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dias_produccion` int NOT NULL DEFAULT '6' COMMENT 'Variable que se usara para calcular fechas en preproyectos',
  `flag_armado` tinyint NOT NULL DEFAULT '1' COMMENT 'Flag para validar si va armado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ind_activo` tinyint NOT NULL DEFAULT '1' COMMENT 'Define si el registro esta activo 1 = activo 0 = in activo',
  PRIMARY KEY (`id`),
  KEY `productos_categoria_id_foreign` (`categoria_id`),
  CONSTRAINT `productos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.producto_caracteristica
CREATE TABLE IF NOT EXISTS `producto_caracteristica` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` bigint unsigned NOT NULL,
  `caracteristica_id` bigint unsigned NOT NULL,
  `flag_armado` tinyint NOT NULL DEFAULT '0' COMMENT '1 = Activo (mostrar en armado), 0 = Inactivo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `producto_caracteristica_producto_id_foreign` (`producto_id`),
  KEY `producto_caracteristica_caracteristica_id_foreign` (`caracteristica_id`),
  CONSTRAINT `producto_caracteristica_caracteristica_id_foreign` FOREIGN KEY (`caracteristica_id`) REFERENCES `caracteristicas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `producto_caracteristica_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.producto_grupo_talla
CREATE TABLE IF NOT EXISTS `producto_grupo_talla` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` bigint unsigned NOT NULL,
  `grupo_talla_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `producto_grupo_talla_producto_id_foreign` (`producto_id`),
  KEY `producto_grupo_talla_grupo_talla_id_foreign` (`grupo_talla_id`),
  CONSTRAINT `producto_grupo_talla_grupo_talla_id_foreign` FOREIGN KEY (`grupo_talla_id`) REFERENCES `grupos_tallas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `producto_grupo_talla_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.proveedores
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint unsigned NOT NULL,
  `nombre_empresa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proveedores_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `proveedores_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.proyectos
CREATE TABLE IF NOT EXISTS `proyectos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint unsigned NOT NULL,
  `direccion_fiscal_id` bigint unsigned DEFAULT NULL,
  `direccion_fiscal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion_entrega_id` bigint unsigned DEFAULT NULL,
  `direccion_entrega` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `id_tipo_envio` bigint DEFAULT NULL COMMENT 'Guarda la referencia del tipo de envío',
  `tipo` enum('PROYECTO','MUESTRA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PROYECTO',
  `numero_muestras` int NOT NULL DEFAULT '0',
  `estado` enum('PENDIENTE','ASIGNADO','EN PROCESO','REVISION','DISEÑO APROBADO','DISEÑO RECHAZADO','CANCELADO') COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_produccion` date DEFAULT NULL,
  `fecha_embarque` date DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `categoria_sel` json DEFAULT NULL,
  `flag_armado` tinyint NOT NULL DEFAULT '1' COMMENT '1 = Activo (Los pedidos seran armados), 0 = Inactivo',
  `producto_sel` json DEFAULT NULL,
  `caracteristicas_sel` json DEFAULT NULL,
  `opciones_sel` json DEFAULT NULL,
  `total_piezas_sel` json DEFAULT NULL COMMENT 'Guarda el total de piezas',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proyectos_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `proyectos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.proyecto_estados
CREATE TABLE IF NOT EXISTS `proyecto_estados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proyecto_id` bigint unsigned NOT NULL,
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_uploaded_file_id` bigint unsigned DEFAULT NULL COMMENT 'Referencia al último archivo cargado en el proyecto',
  `fecha_inicio` timestamp NULL DEFAULT NULL,
  `fecha_fin` timestamp NULL DEFAULT NULL,
  `usuario_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proyecto_estados_proyecto_id_foreign` (`proyecto_id`),
  KEY `proyecto_estados_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `proyecto_estados_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `proyecto_estados_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.proyecto_referencia
CREATE TABLE IF NOT EXISTS `proyecto_referencia` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proyecto_id` bigint unsigned NOT NULL,
  `proyecto_origen_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `proyecto_referencia_proyecto_id_proyecto_origen_id_unique` (`proyecto_id`,`proyecto_origen_id`),
  KEY `proyecto_referencia_proyecto_origen_id_foreign` (`proyecto_origen_id`),
  CONSTRAINT `proyecto_referencia_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `proyecto_referencia_proyecto_origen_id_foreign` FOREIGN KEY (`proyecto_origen_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.role_has_permissions
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.sucursales
CREATE TABLE IF NOT EXISTS `sucursales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sucursales_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `sucursales_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.sucursal_user
CREATE TABLE IF NOT EXISTS `sucursal_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sucursal_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sucursal_user_sucursal_id_foreign` (`sucursal_id`),
  KEY `sucursal_user_user_id_foreign` (`user_id`),
  CONSTRAINT `sucursal_user_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sucursal_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.tallas
CREATE TABLE IF NOT EXISTS `tallas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ind_activo` tinyint NOT NULL DEFAULT '1' COMMENT 'Define si el registro esta activo 1 = activo 0 = in activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.tareas
CREATE TABLE IF NOT EXISTS `tareas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proyecto_id` bigint unsigned NOT NULL,
  `staff_id` bigint unsigned NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `estado` enum('PENDIENTE','EN PROCESO','COMPLETADA','RECHAZADO','CANCELADO') COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `tipo` enum('DISEÑO','PRODUCCION','CORTE','PINTURA','FACTURACION','INDEFINIDA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INDEFINIDA',
  `disenio_flag_first_proceso` tinyint NOT NULL DEFAULT '0' COMMENT 'Marca si es el primer proceso de diseño (0 = No, 1 = Sí)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tareas_proyecto_id_foreign` (`proyecto_id`),
  KEY `tareas_staff_id_foreign` (`staff_id`),
  CONSTRAINT `tareas_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tareas_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.tareas_produccion
CREATE TABLE IF NOT EXISTS `tareas_produccion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `orden_paso_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned NOT NULL,
  `crete_user` bigint unsigned NOT NULL,
  `tipo` enum('DISEÑO','CORTE','BORDADO','PINTURA','FACTURACION','INDEFINIDA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INDEFINIDA',
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('PENDIENTE','EN PROCESO','FINALIZADO','CANCELADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `disenio_flag_first_proceso` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_inicio` timestamp NULL DEFAULT NULL,
  `fecha_fin` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tareas_produccion_usuario_id_foreign` (`usuario_id`),
  KEY `tareas_produccion_crete_user_foreign` (`crete_user`),
  KEY `tareas_produccion_orden_paso_id_foreign` (`orden_paso_id`),
  CONSTRAINT `tareas_produccion_crete_user_foreign` FOREIGN KEY (`crete_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tareas_produccion_orden_paso_id_foreign` FOREIGN KEY (`orden_paso_id`) REFERENCES `orden_paso` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tareas_produccion_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.tipo_envio
CREATE TABLE IF NOT EXISTS `tipo_envio` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `dias_envio` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `config` json DEFAULT NULL COMMENT 'Configuraciones personalizadas como flags',
  `user_can_sel_preproyectos` json DEFAULT NULL,
  `subordinados` json DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  KEY `users_empresa_id_foreign` (`empresa_id`),
  KEY `users_sucursal_id_foreign` (`sucursal_id`),
  CONSTRAINT `users_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.usuario_staf_ordenes_produccion
CREATE TABLE IF NOT EXISTS `usuario_staf_ordenes_produccion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `orden_produccion_id` bigint unsigned NOT NULL,
  `create_user` bigint unsigned NOT NULL,
  `assigned_user_id` bigint unsigned DEFAULT NULL,
  `cantidad_entregada` int NOT NULL DEFAULT '0',
  `cantidad_desperdicio` int NOT NULL DEFAULT '0',
  `total_entregado` int NOT NULL DEFAULT '0',
  `flag_activo` tinyint NOT NULL DEFAULT '0' COMMENT '0 = Inactivo, 1 = Activo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_staf_ordenes_produccion_orden_produccion_id_foreign` (`orden_produccion_id`),
  KEY `usuario_staf_ordenes_produccion_create_user_foreign` (`create_user`),
  KEY `usuario_staf_ordenes_produccion_assigned_user_id_foreign` (`assigned_user_id`),
  CONSTRAINT `usuario_staf_ordenes_produccion_assigned_user_id_foreign` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `usuario_staf_ordenes_produccion_create_user_foreign` FOREIGN KEY (`create_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `usuario_staf_ordenes_produccion_orden_produccion_id_foreign` FOREIGN KEY (`orden_produccion_id`) REFERENCES `ordenes_produccion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla mtiadmin_portal.websockets_statistics_entries
CREATE TABLE IF NOT EXISTS `websockets_statistics_entries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `app_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `peak_connection_count` int NOT NULL,
  `websocket_message_count` int NOT NULL,
  `api_message_count` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
