// Josue Cardona 18/08/2025
// Añado los permisos para la administracion de produccion 

-- asideAprobacionesEspeciales
-- asideAdministraciónMuestras
-- asideAdministraciónPedidos
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (47, 'asideAprobacionesEspeciales', 'web', '2025-08-18 17:59:05', '2025-08-18 17:59:05');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (48, 'asideAdministraciónMuestras', 'web', '2025-08-18 17:59:14', '2025-08-18 17:59:14');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (49, 'asideAdministraciónPedidos', 'web', '2025-08-18 17:59:20', '2025-08-18 17:59:20');


-- asideAdministraciónMuestrasTabPendiente
-- asideAdministraciónMuestrasTabSoliocitada
-- asideAdministraciónMuestrasTabMuestraLista
-- asideAdministraciónMuestrasTabEntregada
-- asideAdministraciónMuestrasTabCancelada

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (54, 'asideAdministraciónMuestrasTabCancelada', 'web', '2025-08-19 11:26:51', '2025-08-19 11:26:51');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (53, 'asideAdministraciónMuestrasTabEntregada', 'web', '2025-08-19 11:26:45', '2025-08-19 11:26:45');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (52, 'asideAdministraciónMuestrasTabMuestraLista', 'web', '2025-08-19 11:26:38', '2025-08-19 11:26:38');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (51, 'asideAdministraciónMuestrasTabSoliocitada', 'web', '2025-08-19 11:26:30', '2025-08-19 11:26:30');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (50, 'asideAdministraciónMuestrasTabPendiente', 'web', '2025-08-19 11:26:24', '2025-08-19 11:26:24');


