 Table users {
  id INT [pk, unique, not null]
  nombre VARCHAR
  correo VARCHAR [unique, not null]
  contrasena VARCHAR [not null]
  tipo_usuario ENUM('ADMINISTRACION', 'STAFF', 'CLIENTES', 'PROVEEDORES')
  rol_id INT [not null, ref: > roles.id]
}

Table roles {
  id INT [pk, unique, not null]
  nombre VARCHAR [not null]
  descripcion TEXT
}

Table permisos {
  id INT [pk, unique, not null]
  nombre VARCHAR [not null]
  descripcion TEXT
}

Table rol_permisos {
  rol_id INT [not null, ref: > roles.id]
  permiso_id INT [not null, ref: > permisos.id]
}

Table paises {
  id INT [pk, unique, not null]
  nombre VARCHAR [not null]
  fecha_creacion TIMESTAMP [default: `now()`]
  fecha_actualizacion TIMESTAMP
}

Table estados {
  id INT [pk, unique, not null]
  pais_id INT [not null, ref: > paises.id]
  nombre VARCHAR [not null]
  fecha_creacion TIMESTAMP [default: `now()`]
  fecha_actualizacion TIMESTAMP
}

Table ciudades {
  id INT [pk, unique, not null]
  estado_id INT [not null, ref: > estados.id]
  nombre VARCHAR [not null]
  fecha_creacion TIMESTAMP [default: `now()`]
  fecha_actualizacion TIMESTAMP
}

Table tipo_envio {
  id INT [pk, unique, not null]
  nombre VARCHAR [not null]
  descripcion TEXT
  dias_envio INT [not null]
  fecha_creacion TIMESTAMP [default: `now()`]
  fecha_actualizacion TIMESTAMP
}

Table ciudades_tipo_envio {
  id INT [pk, unique, not null]
  ciudad_id INT [not null, ref: > ciudades.id]
  tipo_envio_id INT [not null, ref: > tipo_envio.id]
  fecha_creacion TIMESTAMP [default: `now()`]
  fecha_actualizacion TIMESTAMP
}

Table direcciones_fiscales {
  id INT [pk, unique, not null]
  user_id INT [not null, ref: > users.id]
  rfc VARCHAR [not null]
  calle VARCHAR [not null]
  pais_id INT [not null, ref: > paises.id]
  ciudad_id INT [not null, ref: > ciudades.id]
  estado_id INT [not null, ref: > estados.id]
  codigo_postal VARCHAR [not null]
  flag_default tiny
  fecha_creacion TIMESTAMP [default: `now()`]
  fecha_actualizacion TIMESTAMP
}

Table direcciones_entrega {
  id INT [pk, unique, not null]
  user_id INT [not null, ref: > users.id]
  nombre_contacto VARCHAR [not null]
  calle VARCHAR [not null]
  pais_id INT [not null, ref: > paises.id]
  ciudad_id INT [not null, ref: > ciudades.id]
  estado_id INT [not null, ref: > estados.id]
  codigo_postal VARCHAR [not null]
  telefono VARCHAR
  flag_default tiny
  fecha_creacion TIMESTAMP [default: `now()`]
  fecha_actualizacion TIMESTAMP
}

Table clientes {
  id INT [pk, unique, not null]
  usuario_id INT [not null, ref: > users.id]
  nombre_empresa VARCHAR
  contacto_principal VARCHAR
}

Table proveedores {
  id INT [pk, unique, not null]
  usuario_id INT [not null, ref: > users.id]
  nombre_empresa VARCHAR
  contacto_principal VARCHAR
}

Table proyectos {
  id INT [pk, unique, not null]
  cliente_id INT [not null, ref: > clientes.id]
  direccion_fiscal VARCHAR
  direccion_entrega VARCHAR
  nombre VARCHAR
  descripcion TEXT
  id_tipo_envio BIGINT [not null, note: 'Guarda la referencia del tipo de envío']
  tipo ENUM('PROYECTO', 'MUESTRA') [default: 'PROYECTO']
  numero_muestras INT [default: 0]
  estado ENUM('PENDIENTE', 'APROBADO', 'PROGRAMADO', 'IMPRESIÓN', 'PRODUCCIÓN', 'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO', 'RECHAZADO')
  fecha_creacion TIMESTAMP [default: `now()`]
  fecha_produccion DATE
  fecha_embarque DATE
  fecha_entrega DATE
  categoria_sel JSON [note: 'Guarda la selección de categorías']
  producto_sel JSON [note: 'Guarda la selección de productos']
  caracteristicas_sel JSON [note: 'Guarda las características seleccionadas']
  opciones_sel JSON [note: 'Guarda las opciones seleccionadas']
  total_piezas_sel JSON [note: 'Guarda el total de piezas']
}

Table tareas {
  id INT [pk, unique, not null]
  proyecto_id INT [not null, ref: > proyectos.id]
 
  staff_id INT [not null, ref: > users.id]
  descripcion TEXT
  estado ENUM('PENDIENTE', 'EN PROCESO', 'COMPLETADA')
  disenio_flag_first_proceso INT
}

Table categorias {
  id INT [pk, unique, not null]
  nombre VARCHAR
}

Table productos {
  id INT [pk, unique, not null]
  nombre VARCHAR
  flag_armado tinyInteger
  dias_produccion Integer

}

Table categoria_producto {
  id INT [pk, unique, not null]
  categoria_id INT [not null, ref: > categorias.id]
  producto_id INT [not null, ref: > productos.id]
}

Table caracteristicas {
  id INT [pk, unique, not null]
 
  nombre VARCHAR
  flag_selccion_multiple tinyInteger
}

Table categoria_caracteristica {
  id INT [pk, unique, not null]
  categoria_id INT [not null, ref: > categorias.id]
  caracteristica_id INT [not null, ref: > caracteristicas.id]
}

Table producto_caracteristica {
  id INT [pk, unique, not null]
  producto_id INT [not null, ref: > productos.id]
  caracteristica_id INT [not null, ref: > caracteristicas.id]
}

Table opciones {
  id INT [pk, unique, not null]
 
  nombre VARCHAR
  pasos INT
  minutoPaso INT
  valoru INT
}

Table caracteristica_opcion {
  id INT [pk, unique, not null]
  restriccion tinyint
  caracteristica_id INT [not null, ref: > caracteristicas.id]
  opcion_id INT [not null, ref: > opciones.id]
}

Table tallas {
  id INT [pk, unique, not null]
  nombre VARCHAR
  descripcion TEXT
}


Table grupos_tallas {
  id INT [pk, unique, not null]
  nombre VARCHAR [not null] 
}

Table grupo_tallas_detalle {
  id INT [pk, unique, not null]
  grupo_talla_id INT [not null, ref: > grupos_tallas.id]
  talla_id INT [not null, ref: > tallas.id]
}

Table producto_grupo_talla {
  id INT [pk, unique, not null]
  producto_id INT [not null, ref: > productos.id]
  grupo_talla_id INT [not null, ref: > grupos_tallas.id]
}

Table pedido {
  id BIGINT [pk, increment]
  proyecto_id BIGINT [ref: > proyectos.id]
  producto_id BIGINT [ref: > productos.id]
  user_id BIGINT [ref: > users.id]
  cliente_id BIGINT [ref: > clientes.id]
  fecha_creacion TIMESTAMP
  total DECIMAL(10,2)
  total_minutos DOUBLE(8,2)
  total_pasos INT
  resumen_tiempos JSON
  estatus VARCHAR(255)
  created_at TIMESTAMP
  updated_at TIMESTAMP
  direccion_fiscal_id BIGINT
  direccion_fiscal VARCHAR(255)
  direccion_entrega_id BIGINT
  direccion_entrega VARCHAR(255)
  id_tipo_envio BIGINT
  tipo ENUM('POR DEFINIR','PEDIDO','MUESTRA')
  estado ENUM('POR APROBAR','APROBADO','ENTREGADO','RECHAZADO','ARCHIVADO','POR REPROGRAMAR')
  estado_produccion ENUM('POR APROBAR','POR PROGRAMAR','PROGRAMADO','IMPRESIÓN','CORTE','COSTURA','ENTREGA','FACTURACIÓN','COMPLETADO','RECHAZADO')
  flag_aprobar_sin_fechas BOOLEAN
  fecha_produccion DATE
  fecha_embarque DATE
  fecha_entrega DATE
  url VARCHAR(255)
  last_uploaded_file_id BIGINT
}

Table ordenes_produccion {
  id bigint [pk]
  create_user bigint [ref: > users.id]
  flujo_id int      [ref: > flujos_produccion.id]
  tipo enum('CORTE','BORDADO','PINTURA','ETIQUETADO','OTRO')
  created_at timestamp
  updated_at timestamp
}

Table pedido_orden_produccion {
  id bigint [pk]
  pedido_id bigint [ref: > pedido.id]
  orden_produccion_id bigint [ref: > ordenes_produccion.id]
  created_at timestamp
  updated_at timestamp
}

Table orden_corte {
  id bigint [pk]
  orden_produccion_id bigint [ref: > ordenes_produccion.id]
  tallas json
  tallas_entregadas json
  total decimal(10,2)
  caracteristicas json
  fecha_inicio date
  created_at timestamp
  updated_at timestamp
}


Table flujos_produccion {
  id int [pk, increment]
  nombre varchar
  descripcion text
  config json        [note: 'Definición dinámica de pasos, grupos y dependencias']
  created_at timestamp
  updated_at timestamp
}

Table orden_paso {
  id bigint [pk, increment]
  orden_produccion_id bigint [ref: > ordenes_produccion.id]
  nombre varchar       [note: 'Nombre del paso, e.g. CORTE, IMPRESIÓN…']
  grupo_paralelo int   [note: 'Mismos valores = se ejecutan juntos']
  estado enum('PENDIENTE','EN_PROCESO','COMPLETADO') [default:'PENDIENTE']
  fecha_inicio timestamp
  fecha_fin timestamp
  created_at timestamp
  updated_at timestamp
}

Table pedido_estados {
  id INT [pk, not null]
  pedido_id INT [not null, ref: > pedido.id]
  estado VARCHAR [not null]
  fecha_inicio TIMESTAMP
  fecha_fin TIMESTAMP
  created_at TIMESTAMP [default: `now()`]
  updated_at TIMESTAMP
}


Table tareas_produccion {
  id bigint [pk, increment]
  orden_paso_id bigint [ref: > orden_paso.id]
  usuario_id bigint   [ref: > users.id]
  descripcion text
  estado enum('PENDIENTE','EN_PROCESO','FINALIZADO','CANCELADO') [default:'PENDIENTE']
  fecha_inicio timestamp
  fecha_fin timestamp
  created_at timestamp
  updated_at timestamp
}

Table pedido_tarea {
  id BIGINT [pk, increment]
  pedido_id BIGINT [ref: > pedido.id]
  tarea_produccion_id BIGINT [ref: > tareas_produccion.id]
  created_at TIMESTAMP
  updated_at TIMESTAMP
}

Table pedido_caracteristicas {
  pedido_id INT [not null, ref: > pedido.id]
  caracteristica_id INT [not null, ref: > caracteristicas.id]
}

Table pedido_opciones {
  pedido_id INT [not null, ref: > pedido.id]
  opcion_id INT [not null, ref: > opciones.id]
}

Table pedido_tallas {
  pedido_id INT [not null, ref: > pedido.id]
   grupo_id INT [not null, ref: > grupos_tallas.id]
  talla_id INT [not null, ref: > tallas.id]
  cantidad INT
}

Table archivos_proyecto {
  id INT [pk, unique, not null]
  proyecto_id INT [not null, ref: > proyectos.id]
  pre_proyecto_id INT [null, ref: > pre_proyectos.id]
  nombre_archivo VARCHAR [not null]
  ruta_archivo VARCHAR [not null]
  tipo_archivo VARCHAR [not null]
  fecha_subida TIMESTAMP [default: `now()`]
  usuario_id INT [not null, ref: > users.id]
}

Table chats {
  id INT [pk, unique, not null]
  proyecto_id INT [not null, ref: > proyectos.id]
  fecha_creacion TIMESTAMP [default: `now()`]
}

Table mensajes_chat {
  id INT [pk, unique, not null]
  chat_id INT [not null, ref: > chats.id]
  usuario_id INT [not null, ref: > users.id]
  mensaje TEXT [not null]
  fecha_envio TIMESTAMP [default: `now()`]
}


Table pre_proyectos {
  id INT [pk, unique, not null]
  usuario_id INT [not null, ref: > clientes.id]
  direccion_fiscal VARCHAR
  direccion_entrega VARCHAR
  nombre VARCHAR
  descripcion TEXT
  id_tipo_envio int
  tipo ENUM('PROYECTO', 'MUESTRA') [default: 'PROYECTO']
  numero_muestras INT [default: 0]
  estado ENUM('PENDIENTE', 'RECHAZADO') [default: 'PENDIENTE']
  fecha_creacion TIMESTAMP [default: `now()`]
  fecha_Produccion DATE
  fecha_embarque DATE
  fecha_entrega DATE
  categoria_sel json
  producto_sel json
  caracteristicas_sel json
  opciones_sel json
  total_sel json
  created_at TIMESTAMP
  updated_at TIMESTAMP
}


Table proyecto_estados {
  id INT [pk, not null]
  proyecto_id INT [not null, ref: > proyectos.id]
  estado VARCHAR [not null]
  comentario text
  url string
  last_uploaded_file_id INT
  fecha_inicio TIMESTAMP
  fecha_fin TIMESTAMP
  created_at TIMESTAMP [default: `now()`]
  updated_at TIMESTAMP
}

Table proyecto_referencias {
  id INT [pk, not null]
  proyecto_id INT [not null, ref: > proyectos.id]
  proyecto_origen_id INT [not null, ref: > proyectos.id]
  created_at TIMESTAMP [default: `now()`]
  updated_at TIMESTAMP
}

