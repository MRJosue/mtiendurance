//// ==============================================
//// MTI Endurance – Esquema DBML para dbdiagram.io
//// (basado en tu export de MySQL 8.4)
//// Nota: ENUMs representados como varchar
//// ==============================================

Table users {
  id bigint [pk, increment]
  name varchar(255)
  email varchar(255) [unique]
  email_verified_at timestamp
  password varchar(255)
  remember_token varchar(100)
  config json
  user_can_sel_preproyectos json
  subordinados json
  empresa_id bigint
  sucursal_id bigint
  created_at timestamp
  updated_at timestamp
  role_id bigint

  Note: 'FK a empresas, sucursales, roles'
}

Table roles {
  id bigint [pk, increment]
  name varchar(255)
  guard_name varchar(255)
  created_at timestamp
  updated_at timestamp

  Indexes {
    (name, guard_name) [unique]
  }
}

Table permissions {
  id bigint [pk, increment]
  name varchar(255)
  guard_name varchar(255)
  created_at timestamp
  updated_at timestamp

  Indexes {
    (name, guard_name) [unique]
  }
}

Table role_has_permissions {
  permission_id bigint
  role_id bigint

  Indexes {
    (permission_id, role_id) [pk]
  }
}

Table model_has_permissions {
  permission_id bigint
  model_type varchar(255)
  model_id bigint

  Indexes {
    (permission_id, model_id, model_type) [pk]
    (model_id, model_type)
  }
}

Table model_has_roles {
  role_id bigint
  model_type varchar(255)
  model_id bigint

  Indexes {
    (role_id, model_id, model_type) [pk]
    (model_id, model_type)
  }
}

Table personal_access_tokens {
  id bigint [pk, increment]
  tokenable_type varchar(255)
  tokenable_id bigint
  name varchar(255)
  token varchar(64) [unique]
  abilities text
  last_used_at timestamp
  expires_at timestamp
  created_at timestamp
  updated_at timestamp

  Indexes {
    (tokenable_type, tokenable_id)
  }
}

Table failed_jobs {
  id bigint [pk, increment]
  uuid varchar(255) [unique]
  connection text
  queue text
  payload longtext
  exception longtext
  failed_at timestamp
}

Table migrations {
  id int [pk, increment]
  migration varchar(255)
  batch int
}

Table notifications {
  id char(36) [pk]
  type varchar(255)
  notifiable_type varchar(255)
  notifiable_id bigint
  data text
  read_at timestamp
  created_at timestamp
  updated_at timestamp

  Indexes {
    (notifiable_type, notifiable_id)
  }
}

Table empresas {
  id bigint [pk, increment]
  nombre varchar(255)
  rfc varchar(255)
  telefono varchar(255)
  direccion varchar(255)
  created_at timestamp
  updated_at timestamp
}

Table sucursales {
  id bigint [pk, increment]
  empresa_id bigint
  nombre varchar(255)
  telefono varchar(255)
  direccion varchar(255)
  created_at timestamp
  updated_at timestamp
}

Table sucursal_user {
  id bigint [pk, increment]
  sucursal_id bigint
  user_id bigint
  created_at timestamp
  updated_at timestamp
}

Table paises {
  id bigint [pk, increment]
  nombre varchar(255)
  created_at timestamp
  updated_at timestamp
}

Table estados {
  id bigint [pk, increment]
  pais_id bigint
  nombre varchar(255)
  created_at timestamp
  updated_at timestamp
}

Table ciudades {
  id bigint [pk, increment]
  estado_id bigint
  nombre varchar(255)
  created_at timestamp
  updated_at timestamp
}

Table tipo_envio {
  id bigint [pk, increment]
  nombre varchar(255)
  descripcion text
  dias_envio int
  created_at timestamp
  updated_at timestamp
}

Table ciudades_tipo_envio {
  id bigint [pk, increment]
  ciudad_id bigint
  tipo_envio_id bigint
  created_at timestamp
  updated_at timestamp
}

Table direcciones_entrega {
  id bigint [pk, increment]
  usuario_id bigint
  nombre_contacto varchar(255)
  calle varchar(255)
  ciudad_id bigint
  estado_id bigint
  pais_id bigint
  codigo_postal varchar(255)
  telefono varchar(255)
  flag_default boolean
  created_at timestamp
  updated_at timestamp
  nombre_empresa varchar(255)
}

Table direcciones_fiscales {
  id bigint [pk, increment]
  usuario_id bigint
  rfc varchar(255)
  calle varchar(255)
  ciudad_id bigint
  estado_id bigint
  pais_id bigint
  codigo_postal varchar(255)
  flag_default boolean
  created_at timestamp
  updated_at timestamp
}

Table clientes {
  id bigint [pk, increment]
  usuario_id bigint
  nombre_empresa varchar(255)
  contacto_principal varchar(255)
  telefono varchar(255)
  email varchar(255) [unique]
  created_at timestamp
  updated_at timestamp
}

Table proveedores {
  id bigint [pk, increment]
  usuario_id bigint
  nombre_empresa varchar(255)
  contacto_principal varchar(255)
  created_at timestamp
  updated_at timestamp
}

Table categorias {
  id bigint [pk, increment]
  nombre varchar(255)
  created_at timestamp
  updated_at timestamp
  flag_tallas boolean
  ind_activo boolean
}

Table productos {
  id bigint [pk, increment]
  categoria_id bigint
  nombre varchar(255)
  dias_produccion int
  flag_armado boolean
  created_at timestamp
  updated_at timestamp
  ind_activo boolean
}

Table producto_caracteristica {
  id bigint [pk, increment]
  producto_id bigint
  caracteristica_id bigint
  flag_armado boolean
  created_at timestamp
  updated_at timestamp
}

Table categoria_producto {
  id bigint [pk, increment]
  categoria_id bigint
  producto_id bigint
  created_at timestamp
  updated_at timestamp
}

Table caracteristicas {
  id bigint [pk, increment]
  nombre varchar(255)
  flag_seleccion_multiple boolean
  created_at timestamp
  updated_at timestamp
  ind_activo boolean
}

Table opciones {
  id bigint [pk, increment]
  nombre varchar(255)
  pasos double
  minutoPaso time
  valoru double
  created_at timestamp
  updated_at timestamp
  ind_activo boolean
}

Table caracteristica_opcion {
  id bigint [pk, increment]
  caracteristica_id bigint
  opcion_id bigint
  created_at timestamp
  updated_at timestamp
}

Table grupos_tallas {
  id bigint [pk, increment]
  nombre varchar(255)
  created_at timestamp
  updated_at timestamp
  ind_activo boolean

  Indexes {
    (nombre) [unique]
  }
}

Table grupo_tallas_detalle {
  id bigint [pk, increment]
  grupo_talla_id bigint
  talla_id bigint
  created_at timestamp
  updated_at timestamp
}

Table tallas {
  id bigint [pk, increment]
  nombre varchar(255)
  descripcion text
  created_at timestamp
  updated_at timestamp
  ind_activo boolean
}

Table producto_grupo_talla {
  id bigint [pk, increment]
  producto_id bigint
  grupo_talla_id bigint
  created_at timestamp
  updated_at timestamp
}

Table pre_proyectos {
  id bigint [pk, increment]
  usuario_id bigint
  direccion_fiscal_id bigint
  direccion_fiscal varchar(255)
  direccion_entrega_id bigint
  direccion_entrega varchar(255)
  nombre varchar(255)
  descripcion text
  id_tipo_envio bigint
  tipo varchar(20) // PROYECTO | MUESTRA
  numero_muestras int
  estado varchar(20) // PENDIENTE | RECHAZADO
  fecha_creacion timestamp
  fecha_produccion date
  fecha_embarque date
  fecha_entrega date
  categoria_sel json
  flag_armado boolean
  producto_sel json
  caracteristicas_sel json
  opciones_sel json
  total_piezas_sel json
  created_at timestamp
  updated_at timestamp
}

Table proyectos {
  id bigint [pk, increment]
  usuario_id bigint
  direccion_fiscal_id bigint
  direccion_fiscal varchar(255)
  direccion_entrega_id bigint
  direccion_entrega varchar(255)
  nombre varchar(255)
  descripcion text
  id_tipo_envio bigint
  tipo varchar(20) // PROYECTO | MUESTRA
  numero_muestras int
  estado varchar(30) // PENDIENTE|ASIGNADO|EN PROCESO|REVISION|DISEÑO APROBADO|DISEÑO RECHAZADO|CANCELADO
  fecha_creacion timestamp
  fecha_produccion date
  fecha_embarque date
  fecha_entrega date
  categoria_sel json
  flag_armado boolean
  producto_sel json
  caracteristicas_sel json
  opciones_sel json
  total_piezas_sel json
  created_at timestamp
  updated_at timestamp
}

Table proyecto_estados {
  id bigint [pk, increment]
  proyecto_id bigint
  estado varchar(255)
  comentario text
  url varchar(255)
  last_uploaded_file_id bigint
  fecha_inicio timestamp
  fecha_fin timestamp
  usuario_id bigint
  created_at timestamp
  updated_at timestamp
}

Table proyecto_referencia {
  id bigint [pk, increment]
  proyecto_id bigint
  proyecto_origen_id bigint
  created_at timestamp
  updated_at timestamp

  Indexes {
    (proyecto_id, proyecto_origen_id) [unique]
  }
}

Table archivos_proyecto {
  id bigint [pk, increment]
  proyecto_id bigint
  pre_proyecto_id bigint
  nombre_archivo varchar(255)
  ruta_archivo varchar(255)
  tipo_archivo varchar(255)
  tipo_carga tinyint
  flag_descarga boolean
  version int
  fecha_subida timestamp
  usuario_id bigint
  created_at timestamp
  updated_at timestamp
  descripcion text
}

Table chats {
  id bigint [pk, increment]
  proyecto_id bigint
  fecha_creacion timestamp
  created_at timestamp
  updated_at timestamp
}

Table mensajes_chat {
  id bigint [pk, increment]
  chat_id bigint
  usuario_id bigint
  mensaje text
  tipo tinyint // 1 chat, 2 evento
  fecha_envio timestamp
  created_at timestamp
  updated_at timestamp
}

Table tareas {
  id bigint [pk, increment]
  proyecto_id bigint
  staff_id bigint
  descripcion text
  estado varchar(20) // PENDIENTE|EN PROCESO|COMPLETADA|RECHAZADO|CANCELADO
  tipo varchar(20) // DISEÑO|PRODUCCION|...
  disenio_flag_first_proceso boolean
  created_at timestamp
  updated_at timestamp
}

Table filtros_produccion {
  id bigint [pk, increment]
  nombre varchar(255)
  slug varchar(255)
  descripcion text
  created_by bigint
  visible boolean
  orden int
  config json
  created_at timestamp
  updated_at timestamp

  Indexes {
    (slug) [unique]
  }
}

Table filtro_produccion_caracteristicas {
  id bigint [pk, increment]
  filtro_produccion_id bigint
  caracteristica_id bigint
  orden int
  label varchar(255)
  visible boolean
  ancho varchar(255)
  render varchar(10) // texto|badges|chips|iconos|count
  multivalor_modo varchar(10) // inline|badges|count
  max_items tinyint
  fallback varchar(255)
  created_at timestamp
  updated_at timestamp

  Indexes {
    (filtro_produccion_id, caracteristica_id) [unique, name: 'ux_filtro_caracteristica']
  }
}

Table filtro_produccion_productos {
  id bigint [pk, increment]
  filtro_produccion_id bigint
  producto_id bigint
  created_at timestamp
  updated_at timestamp

  Indexes {
    (filtro_produccion_id, producto_id) [unique, name: 'ux_filtro_producto']
  }
}

Table flujos_produccion {
  id bigint [pk, increment]
  nombre varchar(100)
  descripcion text
  config json
  created_at timestamp
  updated_at timestamp
}

Table ordenes_produccion {
  id bigint [pk, increment]
  flujo_id int
  create_user bigint
  assigned_user_id bigint
  tipo varchar(15) // CORTE|SUBLIMADO|...
  estado varchar(15) // SIN INICIAR|EN PROCESO|TERMINADO|CANCELADO
  flag_activo boolean
  prioridad tinyint
  fecha_sin_iniciar timestamp
  fecha_en_proceso timestamp
  fecha_terminado timestamp
  fecha_cancelado timestamp
  created_at timestamp
  updated_at timestamp
}

Table orden_paso {
  id bigint [pk, increment]
  orden_produccion_id bigint
  nombre varchar(100)
  grupo_paralelo int
  estado varchar(15) // PENDIENTE|EN_PROCESO|COMPLETADO
  fecha_inicio timestamp
  fecha_fin timestamp
  created_at timestamp
  updated_at timestamp
}

Table tareas_produccion {
  id bigint [pk, increment]
  orden_paso_id bigint
  usuario_id bigint
  crete_user bigint
  tipo varchar(15) // DISEÑO|CORTE|...
  descripcion varchar(255)
  estado varchar(15) // PENDIENTE|EN PROCESO|FINALIZADO|CANCELADO
  disenio_flag_first_proceso boolean
  fecha_inicio timestamp
  fecha_fin timestamp
  created_at timestamp
  updated_at timestamp
}

Table usuario_staf_ordenes_produccion {
  id bigint [pk, increment]
  orden_produccion_id bigint
  create_user bigint
  assigned_user_id bigint
  cantidad_entregada int
  cantidad_desperdicio int
  total_entregado int
  flag_activo boolean
  created_at timestamp
  updated_at timestamp
}

Table orden_corte {
  id bigint [pk, increment]
  orden_produccion_id bigint
  tallas json
  tallas_entregadas json
  total decimal(10,2)
  caracteristicas json
  fecha_inicio date
  created_at timestamp
  updated_at timestamp
}

Table pedido {
  id bigint [pk, increment]
  proyecto_id bigint
  producto_id bigint
  user_id bigint
  cliente_id bigint
  fecha_creacion timestamp
  total decimal(10,2)
  total_minutos double
  total_pasos int
  resumen_tiempos json
  estatus varchar(255)
  descripcion_pedido text
  instrucciones_muestra text
  flag_facturacion boolean
  created_at timestamp
  updated_at timestamp
  direccion_fiscal_id bigint
  direccion_fiscal varchar(255)
  direccion_entrega_id bigint
  direccion_entrega varchar(255)
  id_tipo_envio bigint
  tipo varchar(15) // POR DEFINIR|PEDIDO|MUESTRA
  estatus_entrega_muestra varchar(10) // PENDIENTE|DIGITAL|FISICA
  estatus_muestra varchar(15) // PENDIENTE|SOLICITADA|MUESTRA LISTA|ENTREGADA|COMPLETADA|CANCELADA
  estado varchar(15) // POR APROBAR|APROBADO|ENTREGADO|RECHAZADO|ARCHIVADO|POR REPROGRAMAR
  estado_produccion varchar(15) // POR APROBAR|POR PROGRAMAR|PROGRAMADO|IMPRESIÓN|CORTE|COSTURA|ENTREGA|FACTURACIÓN|COMPLETADO|RECHAZADO
  flag_aprobar_sin_fechas boolean
  flag_solicitud_aprobar_sin_fechas boolean
  fecha_produccion date
  fecha_embarque date
  fecha_entrega date
  url varchar(255)
  last_uploaded_file_id bigint
}

Table pedido_estados {
  id bigint [pk, increment]
  pedido_id bigint
  proyecto_id bigint
  usuario_id bigint
  estado varchar(255)
  comentario text
  fecha_inicio timestamp
  fecha_fin timestamp
  created_at timestamp
  updated_at timestamp
}

Table pedido_caracteristicas {
  pedido_id bigint
  caracteristica_id bigint
  created_at timestamp
  updated_at timestamp

  Indexes {
    (pedido_id, caracteristica_id) [pk]
  }
}

Table pedido_opciones {
  id bigint [pk, increment]
  pedido_id bigint
  opcion_id bigint
  valor varchar(255)
  created_at timestamp
  updated_at timestamp
}

Table pedido_tallas {
  id bigint [pk, increment]
  pedido_id bigint
  talla_id bigint
  grupo_talla_id bigint
  cantidad int
}

Table pedido_tarea {
  id bigint [pk, increment]
  pedido_id bigint
  tarea_produccion_id bigint
  created_at timestamp
  updated_at timestamp
}

Table pedido_orden_produccion {
  id bigint [pk, increment]
  pedido_id bigint
  orden_produccion_id bigint
  created_at timestamp
  updated_at timestamp
}

Table archivos_pedido {
  id bigint [pk, increment]
  pedido_id bigint
  nombre_archivo varchar(255)
  ruta_archivo varchar(255)
  tipo_archivo varchar(255)
  tipo_carga tinyint // 1=general,2=otro,3=evidencia
  flag_descarga boolean
  usuario_id bigint
  descripcion varchar(255)
  version int
  created_at timestamp
  updated_at timestamp
}

Table layouts {
  id bigint [pk, increment]
  nombre varchar(255)
  descripcion text
  producto_id bigint
  categoria_id bigint
  usuario_id bigint
  ind_activo boolean
  created_at timestamp
  updated_at timestamp
}

Table layout_elementos {
  id bigint [pk, increment]
  layout_id bigint
  tipo varchar(255)
  caracteristica_id bigint
  letra varchar(5)
  posicion_x int
  posicion_y int
  ancho int
  alto int
  orden int
  configuracion json
  created_at timestamp
  updated_at timestamp
}

Table grupos_orden {
  id bigint [pk, increment]
  nombre varchar(255)

  created_at timestamp
  updated_at timestamp

  Indexes {
    (nombre) [unique]
  }
}

Table grupo_orden_permission {
  id bigint [pk, increment]
  grupo_orden_id bigint
  permission_id bigint
  orden int
  created_at timestamp
  updated_at timestamp

  Indexes {
    (grupo_orden_id, permission_id) [unique]
  }
}


Table websokets_statistics_entries {
  id int [pk, increment]
  app_id varchar(255)
  peak_connection_count int
  websocket_message_count int
  api_message_count int
  created_at timestamp
  updated_at timestamp
}

// password_reset_tokens (Laravel)
Table password_reset_tokens {
  email varchar(255) [pk]
  token varchar(255)
  created_at timestamp
}

//// =========================
//// Relaciones (Ref)
//// =========================

// users
Ref: users.role_id > roles.id
Ref: users.empresa_id > empresas.id
Ref: users.sucursal_id > sucursales.id

// sucursales
Ref: sucursales.empresa_id > empresas.id
Ref: sucursal_user.sucursal_id > sucursales.id
Ref: sucursal_user.user_id > users.id

// geografía y envíos
Ref: estados.pais_id > paises.id
Ref: ciudades.estado_id > estados.id
Ref: direcciones_entrega.ciudad_id > ciudades.id
Ref: direcciones_entrega.estado_id > estados.id
Ref: direcciones_entrega.pais_id > paises.id
Ref: direcciones_entrega.usuario_id > users.id
Ref: direcciones_fiscales.ciudad_id > ciudades.id
Ref: direcciones_fiscales.estado_id > estados.id
Ref: direcciones_fiscales.pais_id > paises.id
Ref: direcciones_fiscales.usuario_id > users.id
Ref: ciudades_tipo_envio.ciudad_id > ciudades.id
Ref: ciudades_tipo_envio.tipo_envio_id > tipo_envio.id

// clientes y proveedores
Ref: clientes.usuario_id > users.id
Ref: proveedores.usuario_id > users.id

// catálogos de productos
Ref: productos.categoria_id > categorias.id
Ref: categoria_producto.categoria_id > categorias.id
Ref: categoria_producto.producto_id > productos.id
Ref: producto_caracteristica.producto_id > productos.id
Ref: producto_caracteristica.caracteristica_id > caracteristicas.id
Ref: caracteristica_opcion.caracteristica_id > caracteristicas.id
Ref: caracteristica_opcion.opcion_id > opciones.id

// tallas
Ref: grupo_tallas_detalle.grupo_talla_id > grupos_tallas.id
Ref: grupo_tallas_detalle.talla_id > tallas.id
Ref: producto_grupo_talla.producto_id > productos.id
Ref: producto_grupo_talla.grupo_talla_id > grupos_tallas.id

// pre_proyectos / proyectos y archivos
Ref: pre_proyectos.usuario_id > users.id
Ref: proyectos.usuario_id > users.id
Ref: archivos_proyecto.proyecto_id > proyectos.id
Ref: archivos_proyecto.pre_proyecto_id > pre_proyectos.id
Ref: archivos_proyecto.usuario_id > users.id

// proyecto estados / referencias / chats
Ref: proyecto_estados.proyecto_id > proyectos.id
Ref: proyecto_estados.usuario_id > users.id
Ref: proyecto_referencia.proyecto_id > proyectos.id
Ref: proyecto_referencia.proyecto_origen_id > proyectos.id
Ref: chats.proyecto_id > proyectos.id
Ref: mensajes_chat.chat_id > chats.id
Ref: mensajes_chat.usuario_id > users.id

// tareas (diseño/gestión)
Ref: tareas.proyecto_id > proyectos.id
Ref: tareas.staff_id > users.id

// filtros de producción
Ref: filtros_produccion.created_by > users.id
Ref: filtro_produccion_caracteristicas.filtro_produccion_id > filtros_produccion.id
Ref: filtro_produccion_caracteristicas.caracteristica_id > caracteristicas.id
Ref: filtro_produccion_productos.filtro_produccion_id > filtros_produccion.id
Ref: filtro_produccion_productos.producto_id > productos.id

// flujos / órdenes / pasos / tareas producción
Ref: orden_paso.orden_produccion_id > ordenes_produccion.id
Ref: tareas_produccion.orden_paso_id > orden_paso.id
Ref: tareas_produccion.usuario_id > users.id
Ref: tareas_produccion.crete_user > users.id
Ref: usuario_staf_ordenes_produccion.orden_produccion_id > ordenes_produccion.id
Ref: usuario_staf_ordenes_produccion.create_user > users.id
Ref: usuario_staf_ordenes_produccion.assigned_user_id > users.id
Ref: orden_corte.orden_produccion_id > ordenes_produccion.id
Ref: ordenes_produccion.assigned_user_id > users.id
Ref: ordenes_produccion.create_user > users.id

// pedidos
Ref: pedido.user_id > users.id
Ref: pedido.proyecto_id > proyectos.id
Ref: pedido.producto_id > productos.id

Ref: pedido_estados.pedido_id > pedido.id
Ref: pedido_estados.proyecto_id > proyectos.id
Ref: pedido_estados.usuario_id > users.id

Ref: pedido_caracteristicas.pedido_id > pedido.id
Ref: pedido_caracteristicas.caracteristica_id > caracteristicas.id

Ref: pedido_opciones.pedido_id > pedido.id
Ref: pedido_opciones.opcion_id > opciones.id

Ref: pedido_tallas.pedido_id > pedido.id
Ref: pedido_tallas.talla_id > tallas.id

