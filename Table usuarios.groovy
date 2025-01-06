Table usuarios {
  id UUID [pk, unique, not null]
  nombre VARCHAR
  correo VARCHAR [unique, not null]
  contrasena VARCHAR [not null]
  tipo_usuario ENUM('ADMINISTRACION', 'STAFF', 'CLIENTES', 'PROVEEDORES')
  rol_id UUID [not null, ref: > roles.id]
}

Table roles {
  id UUID [pk, unique, not null]
  nombre VARCHAR [not null]
  descripcion TEXT
}

Table permisos {
  id UUID [pk, unique, not null]
  nombre VARCHAR [not null]
  descripcion TEXT
}

Table rol_permisos {
  rol_id UUID [not null, ref: > roles.id]
  permiso_id UUID [not null, ref: > permisos.id]
}

Table clientes {
  id UUID [pk, unique, not null]
  usuario_id UUID [not null, ref: > usuarios.id]
  nombre_empresa VARCHAR
  contacto_principal VARCHAR
}

Table proveedores {
  id UUID [pk, unique, not null]
  usuario_id UUID [not null, ref: > usuarios.id]
  nombre_empresa VARCHAR
  contacto_principal VARCHAR
}

Table proyectos {
  id UUID [pk, unique, not null]
  cliente_id UUID [not null, ref: > clientes.id]
  nombre VARCHAR
  descripcion TEXT
  estado ENUM('PENDIENTE', 'APROBADO', 'PROGRAMADO', 'IMPRESIÓN', 'PRODUCCIÓN', 'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO')
  fecha_creacion TIMESTAMP [default: `now()`]
  fecha_entrega DATE
}

Table tareas {
  id UUID [pk, unique, not null]
  proyecto_id UUID [not null, ref: > proyectos.id]
  staff_id UUID [not null, ref: > usuarios.id]
  descripcion TEXT
  estado ENUM('PENDIENTE', 'EN PROCESO', 'COMPLETADA')
}

Table categorias {
  id UUID [pk, unique, not null]
  nombre VARCHAR
}

Table productos {
  id UUID [pk, unique, not null]
  nombre VARCHAR
  categoria_id UUID [not null, ref: > categorias.id]
}

Table caracteristicas {
  id UUID [pk, unique, not null]
  producto_id UUID [not null, ref: > productos.id]
  nombre VARCHAR
}

Table opciones {
  id UUID [pk, unique, not null]
  caracteristica_id UUID [not null, ref: > caracteristicas.id]
  valor VARCHAR
}

Table tallas {
  id UUID [pk, unique, not null]
  nombre VARCHAR
  descripcion TEXT
}



Table pedido {
  id UUID [pk, unique, not null]
  proyecto_id UUID [not null, unique, ref: > proyectos.id]
  producto_id UUID [not null, ref: > productos.id]
  fecha_creacion TIMESTAMP [default: `now()`]
}


Table pedido_caracteristicas {
  pedido_id UUID [not null, ref: > pedido.id]
  caracteristica_id UUID [not null, ref: > caracteristicas.id]
}

Table pedido_opciones {
  pedido_id UUID [not null, ref: > pedido.id]
  opcion_id UUID [not null, ref: > opciones.id]
}

Table pedido_tallas {
  pedido_id UUID [not null, ref: > pedido.id]
  talla_id UUID [not null, ref: > tallas.id]
  cantidad INT
}

Ref: "pedido"."id" < "tareas"."descripcion"
