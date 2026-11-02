
CREATE DATABASE IF NOT EXISTS condia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE condia;

-- Condominio administrado
CREATE TABLE condominios (
  id_condominio INT NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  tipo VARCHAR(50) NOT NULL,
  direccion VARCHAR(255),
  telefono VARCHAR(20),
  PRIMARY KEY (id_condominio),
  UNIQUE KEY uk_condominios_nombre (nombre),
  CONSTRAINT chk_condominios_tipo CHECK (tipo IN ('edificio','residencial'))
) ENGINE=InnoDB;

-- Apartamentos o casas del condominio.
CREATE TABLE unidades (
  id_unidad INT NOT NULL AUTO_INCREMENT,
  id_condominio INT NOT NULL,
  numero VARCHAR(20) NOT NULL,
  torre_bloque VARCHAR(50),
  nivel VARCHAR(20),
  PRIMARY KEY (id_unidad),
  CONSTRAINT fk_unidades_id_condominio FOREIGN KEY (id_condominio) REFERENCES condominios(id_condominio)
) ENGINE=InnoDB;

-- tabla de usuarios.
CREATE TABLE usuarios (
  id_usuario INT NOT NULL AUTO_INCREMENT,
  correo VARCHAR(150) NOT NULL,
  password VARCHAR(255) NOT NULL,
  rol VARCHAR(30) NOT NULL,
  activo BOOLEAN NOT NULL DEFAULT 1,
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uk_usuarios_correo (correo),
  CONSTRAINT chk_usuarios_rol CHECK (rol IN ('administrador','residente'))
) ENGINE=InnoDB;

-- tabla de residentes del condominio.
CREATE TABLE residentes (
  id_residente INT NOT NULL AUTO_INCREMENT,
  id_usuario INT,
  id_unidad INT NOT NULL,
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  telefono VARCHAR(20),
  fecha_ingreso DATE NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'activo',
  PRIMARY KEY (id_residente),
  UNIQUE KEY uk_residentes_id_usuario (id_usuario),
  CONSTRAINT fk_residentes_id_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
  CONSTRAINT fk_residentes_id_unidad FOREIGN KEY (id_unidad) REFERENCES unidades(id_unidad),
  CONSTRAINT chk_residentes_estado CHECK (estado IN ('activo','inactivo'))
) ENGINE=InnoDB;

-- Cuotas asignadas a los residentes.
CREATE TABLE cuotas (
  id_cuota INT NOT NULL AUTO_INCREMENT,
  id_residente INT NOT NULL,
  concepto VARCHAR(100) NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  periodo CHAR(7) NOT NULL,
  fecha_vencimiento DATE NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
  PRIMARY KEY (id_cuota),
  CONSTRAINT fk_cuotas_id_residente FOREIGN KEY (id_residente) REFERENCES residentes(id_residente),
  CONSTRAINT chk_cuotas_monto CHECK (monto > 0),
  CONSTRAINT chk_cuotas_estado CHECK (estado IN ('pendiente','pagada','vencida'))
) ENGINE=InnoDB;

-- Pagos registrados.
CREATE TABLE pagos (
  id_pago INT NOT NULL AUTO_INCREMENT,
  id_cuota INT NOT NULL,
  id_usuario_registro INT NOT NULL,
  monto_pagado DECIMAL(10,2) NOT NULL,
  fecha_pago DATE NOT NULL,
  metodo_pago VARCHAR(30) NOT NULL,
  referencia VARCHAR(50),
  PRIMARY KEY (id_pago),
  CONSTRAINT fk_pagos_id_cuota FOREIGN KEY (id_cuota) REFERENCES cuotas(id_cuota),
  CONSTRAINT fk_pagos_id_usuario_registro FOREIGN KEY (id_usuario_registro) REFERENCES usuarios(id_usuario),
  CONSTRAINT chk_pagos_monto_pagado CHECK (monto_pagado > 0),
  CONSTRAINT chk_pagos_metodo_pago CHECK (metodo_pago IN ('efectivo','transferencia','cheque'))
) ENGINE=InnoDB;

-- Quejas presentadas por los residentes.
CREATE TABLE quejas (
  id_queja INT NOT NULL AUTO_INCREMENT,
  id_residente INT NOT NULL,
  asunto VARCHAR(150) NOT NULL,
  categoria VARCHAR(50) NOT NULL,
  descripcion TEXT NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
  respuesta TEXT,
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_queja),
  CONSTRAINT fk_quejas_id_residente FOREIGN KEY (id_residente) REFERENCES residentes(id_residente),
  CONSTRAINT chk_quejas_categoria CHECK (categoria IN ('convivencia','limpieza','seguridad','ruido','infraestructura','otra')),
  CONSTRAINT chk_quejas_estado CHECK (estado IN ('pendiente','en_proceso','resuelta'))
) ENGINE=InnoDB;

-- Personal de mantenimiento y seguridad.
CREATE TABLE personal (
  id_personal INT NOT NULL AUTO_INCREMENT,
  id_condominio INT NOT NULL,
  id_usuario INT,
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  cargo VARCHAR(50) NOT NULL,
  turno VARCHAR(30) NOT NULL,
  telefono VARCHAR(20),
  estado VARCHAR(30) NOT NULL DEFAULT 'activo',
  PRIMARY KEY (id_personal),
  CONSTRAINT fk_personal_id_condominio FOREIGN KEY (id_condominio) REFERENCES condominios(id_condominio),
  UNIQUE KEY uk_personal_id_usuario (id_usuario),
  CONSTRAINT fk_personal_id_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
  CONSTRAINT chk_personal_cargo CHECK (cargo IN ('mantenimiento','seguridad')),
  CONSTRAINT chk_personal_turno CHECK (turno IN ('diurno','nocturno','mixto')),
  CONSTRAINT chk_personal_estado CHECK (estado IN ('activo','inactivo'))
) ENGINE=InnoDB;

-- Solicitudes de mantenimiento de las instalaciones.
CREATE TABLE solicitudes_mantenimiento (
  id_solicitud INT NOT NULL AUTO_INCREMENT,
  id_residente INT NOT NULL,
  id_personal INT,
  titulo VARCHAR(150) NOT NULL,
  ubicacion VARCHAR(150) NOT NULL,
  prioridad VARCHAR(20) NOT NULL DEFAULT 'media',
  estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
  descripcion TEXT NOT NULL,
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_solicitud),
  CONSTRAINT fk_solicitudes_mantenimiento_id_residente FOREIGN KEY (id_residente) REFERENCES residentes(id_residente),
  CONSTRAINT fk_solicitudes_mantenimiento_id_personal FOREIGN KEY (id_personal) REFERENCES personal(id_personal),
  CONSTRAINT chk_solicitudes_mantenimiento_prioridad CHECK (prioridad IN ('baja','media','alta')),
  CONSTRAINT chk_solicitudes_mantenimiento_estado CHECK (estado IN ('pendiente','en_proceso','completada'))
) ENGINE=InnoDB;
