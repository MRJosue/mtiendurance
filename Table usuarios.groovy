Table usuarios {
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

Table clientes {
  id INT [pk, unique, not null]
  usuario_id INT [not null, ref: > usuarios.id]
  nombre_empresa VARCHAR
  contacto_principal VARCHAR
}

Table proveedores {
  id INT [pk, unique, not null]
  usuario_id INT [not null, ref: > usuarios.id]
  nombre_empresa VARCHAR
  contacto_principal VARCHAR
}

Table proyectos {
  id INT [pk, unique, not null]
  cliente_id INT [not null, ref: > clientes.id]
  nombre VARCHAR
  descripcion TEXT
  estado ENUM('PENDIENTE', 'APROBADO', 'PROGRAMADO', 'IMPRESIÓN', 'PRODUCCIÓN', 'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO')
  fecha_creacion TIMESTAMP [default: `now()`]
  fecha_entrega DATE
}

Table tareas {
  id INT [pk, unique, not null]
  proyecto_id INT [not null, ref: > proyectos.id]
  staff_id INT [not null, ref: > usuarios.id]
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
  pasos int
  minutoPaso int
  valoru int
}

Table opciones {
  id INT [pk, unique, not null]
  caracteristica_id INT [not null, ref: > caracteristicas.id]
  nombre VARCHAR
  pasos int
  minutoPaso int
  valoru int
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
  totalpasos   int
  totalminutoPaso int
  totalvalor   int
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
  usuario_id INT [not null, ref: > usuarios.id]
}



Table salas_chat {
  id INT [pk, unique, not null]
  proyecto_id INT [not null, ref: > proyectos.id]
  nombre VARCHAR
  fecha_creacion TIMESTAMP [default: `now()`]
}

Table participantes_chat {
  id INT [pk, unique, not null]
  sala_chat_id INT [not null, ref: > salas_chat.id]
  usuario_id INT [not null, ref: > usuarios.id]
  fecha_ingreso TIMESTAMP [default: `now()`]
}

Table mensajes_chat {
  id INT [pk, unique, not null]
  sala_chat_id INT [not null, ref: > salas_chat.id]
  usuario_id INT [not null, ref: > usuarios.id]
  mensaje TEXT [not null]
  fecha_envio TIMESTAMP [default: `now()`]
}


Ref: "pedido"."id" < "tareas"."descripcion"


Ref: "pedido"."producto_id" < "proyectos"."descripcion"
