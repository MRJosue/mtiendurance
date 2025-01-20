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

Table direcciones_fiscales {
  id INT [pk, unique, not null]
  user_id INT [not null, ref: > users.id]
  rfc VARCHAR [not null]
  calle VARCHAR [not null]
  ciudad VARCHAR [not null]
  estado VARCHAR [not null]
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
  ciudad VARCHAR [not null]
  estado VARCHAR [not null]
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
  referencia INT
  tipo ENUM('PROYECTO', 'MUESTRA') [default: 'PROYECTO']
  numero_muestras INT [default: 0]
  estado ENUM('PENDIENTE', 'APROBADO', 'PROGRAMADO', 'IMPRESIÓN', 'PRODUCCIÓN', 'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO')
  fecha_creacion TIMESTAMP [default: `now()`]
  fecha_Produccion DATE
  fecha_embarque DATE
  fecha_entrega DATE
}

Table tareas {
  id INT [pk, unique, not null]
  proyecto_id INT [not null, ref: > proyectos.id]
  staff_id INT [not null, ref: > users.id]
  descripcion TEXT
  estado ENUM('PENDIENTE', 'EN PROCESO', 'COMPLETADA')
}

Table categorias {
  id INT [pk, unique, not null]
  nombre VARCHAR
}

Table productos {
  id INT [pk, unique, not null]
  nombre VARCHAR
  categoria_id INT [not null, ref: > categorias.id]
}

Table caracteristicas {
  id INT [pk, unique, not null]
  producto_id INT [not null, ref: > productos.id]
  nombre VARCHAR
  pasos INT
  minutoPaso INT
  valoru INT
}

Table opciones {
  id INT [pk, unique, not null]
  caracteristica_id INT [not null, ref: > caracteristicas.id]
  nombre VARCHAR
  pasos INT
  minutoPaso INT
  valoru INT
}

Table tallas {
  id INT [pk, unique, not null]
  nombre VARCHAR
  descripcion TEXT
}

Table pedido {
  id INT [pk, unique, not null]
  proyecto_id INT [not null, unique, ref: > proyectos.id] 
  producto_id INT [not null, ref: > productos.id]
  fecha_creacion TIMESTAMP [default: `now()`]
  totalpasos INT
  totalminutoPaso INT
  totalvalor INT
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
  talla_id INT [not null, ref: > tallas.id]
  cantidad INT
}

Table archivos_proyecto {
  id INT [pk, unique, not null]
  proyecto_id INT [not null, ref: > proyectos.id]
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
  tipo ENUM('PROYECTO', 'MUESTRA') [default: 'PROYECTO']
  numero_muestras INT [default: 0]
  estado ENUM('PENDIENTE', 'RECHAZADO') [default: 'PENDIENTE']
  fecha_creacion TIMESTAMP [default: `now()`]
  fecha_Produccion DATE
  fecha_embarque DATE
  fecha_entrega DATE
  created_at TIMESTAMP
  updated_at TIMESTAMP
}


Table proyecto_estados {
  id INT [pk, not null]
  proyecto_id INT [not null, ref: > proyectos.id]
  estado VARCHAR [not null]
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
