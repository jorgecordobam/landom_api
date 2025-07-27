-- SQL Script para la creación de la base de datos LandonPro (MySQL)
-- Creado: 25 de junio de 2025

-- Borra la base de datos si ya existe para asegurar una creación limpia.
-- ¡ADVERTENCIA! Esto eliminará todos los datos existentes si la base de datos ya existe.
DROP DATABASE IF EXISTS `landonpro_db`;

-- Crea la base de datos con codificación UTF-8 para soporte de caracteres diversos.
CREATE DATABASE IF NOT EXISTS `landonpro_db`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Selecciona la base de datos para trabajar.
USE `landonpro_db`;

-- -----------------------------------------------------
-- Tabla `usuarios`
-- Almacena la información base de todos los usuarios de la plataforma.
-- -----------------------------------------------------
CREATE TABLE `usuarios` (
    `id_usuario` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del usuario',
    `nombre` VARCHAR(100) NOT NULL,
    `apellido` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Correo electrónico para login y notificaciones',
    `contrasena_hash` VARCHAR(255) NOT NULL COMMENT 'Contraseña cifrada (se recomienda bcrypt o similar)',
    `telefono` VARCHAR(50) NULL,
    `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de registro del usuario',
    `tipo_perfil` ENUM('Inversor', 'Trabajador', 'ConstructorContratista', 'General') NOT NULL COMMENT 'Tipo de perfil del usuario',
    `estado_verificacion` ENUM('Pendiente', 'En Revisión', 'Verificado', 'Rechazado') DEFAULT 'Pendiente' COMMENT 'Estado del proceso de verificación de documentos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla base para todos los usuarios';

-- -----------------------------------------------------
-- Tabla `perfiles_inversores`
-- Detalles y documentos específicos requeridos para el perfil de Inversor.
-- -----------------------------------------------------
CREATE TABLE `perfiles_inversores` (
    `id_perfil_inversor` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del perfil de inversor',
    `id_usuario` INT NOT NULL UNIQUE COMMENT 'FK a usuarios, unique para relación 1:1',
    `url_id_oficial` VARCHAR(500) NULL COMMENT 'URL al documento de identificación (Cédula o pasaporte)',
    `url_prueba_fondos` VARCHAR(500) NULL COMMENT 'URL a prueba de fondos (bank statement, carta bancaria)',
    `url_formulario_tributario` VARCHAR(500) NULL COMMENT 'URL a formulario W-9 (EE.UU.) o su equivalente tributario',
    `url_contrato_inversion_marco` VARCHAR(500) NULL COMMENT 'URL a contrato de inversión firmado',
    `url_perfil_riesgo` VARCHAR(500) NULL COMMENT 'URL a formulario de evaluación del perfil de riesgo',
    `url_verificacion_direccion` VARCHAR(500) NULL COMMENT 'URL a verificación de dirección (factura de servicios o lease)',
    `es_acreditado` BOOLEAN DEFAULT FALSE COMMENT 'Indica si es un inversor acreditado (si aplica)',
    `url_antecedentes_financieros_legales` VARCHAR(500) NULL COMMENT 'Reporte de antecedentes financieros o legales (opcional)',
    CONSTRAINT `fk_perfil_inversor_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de perfiles de inversores';

-- -----------------------------------------------------
-- Tabla `perfiles_trabajadores`
-- Detalles y documentos específicos para obreros, técnicos, arquitectos, supervisores.
-- -----------------------------------------------------
CREATE TABLE `perfiles_trabajadores` (
    `id_perfil_trabajador` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del perfil de trabajador',
    `id_usuario` INT NOT NULL UNIQUE COMMENT 'FK a usuarios, unique para relación 1:1',
    `url_id_oficial` VARCHAR(500) NULL COMMENT 'URL a identificación oficial (ID estatal o licencia de conducir)',
    `numero_seguro_social_itin_hash` VARCHAR(255) NULL COMMENT 'Número de Seguro Social o ITIN (EE.UU.) - ENCRIPTADO',
    `url_certificados_capacitacion` JSON NULL COMMENT 'Array de URLs a certificados de capacitación técnica (OSHA, construcción, etc.)',
    `url_curriculum` VARCHAR(500) NULL COMMENT 'URL a currículum o ficha de experiencia laboral',
    `experiencia_laboral` TEXT NULL COMMENT 'Descripción de la experiencia laboral o referencias comprobables',
    `url_foto_carnet` VARCHAR(500) NULL COMMENT 'URL a foto tipo carnet actualizada',
    `disponibilidad_actual` ENUM('Disponible', 'Ocupado', 'De Vacaciones', 'No Asignado') DEFAULT 'Disponible' COMMENT 'Estado de disponibilidad del trabajador',
    CONSTRAINT `fk_perfil_trabajador_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de perfiles de trabajadores (obreros, técnicos, etc.)';

-- -----------------------------------------------------
-- Tabla `perfiles_constructores_contratistas`
-- Detalles y documentos para compañías Constructoras / Contratistas.
-- -----------------------------------------------------
CREATE TABLE `perfiles_constructores_contratistas` (
    `id_perfil_constructor_contratista` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del perfil de constructor/contratista',
    `id_usuario` INT NOT NULL UNIQUE COMMENT 'FK a usuarios, unique para relación 1:1 (representante legal o principal de la empresa)',
    `nombre_empresa` VARCHAR(255) NOT NULL,
    `nit_o_registro_empresa` VARCHAR(100) UNIQUE NULL COMMENT 'Número de identificación tributaria o registro de la empresa',
    `url_certificado_registro_empresa` VARCHAR(500) NULL COMMENT 'URL a certificado de registro de la empresa (Articles of Organization)',
    `url_licencia_contratista` VARCHAR(500) NULL COMMENT 'URL a licencia de contratista general vigente',
    `url_seguro_responsabilidad` VARCHAR(500) NULL COMMENT 'URL a seguro de responsabilidad civil (general liability insurance)',
    `url_seguro_compensacion` VARCHAR(500) NULL COMMENT 'URL a seguro de compensación laboral (worker’s comp)',
    `url_portafolio_proyectos` JSON NULL COMMENT 'Array de URLs a lista de proyectos anteriores (portafolio o dossier técnico)',
    `contacto_legal_nombre` VARCHAR(255) NULL COMMENT 'Nombre del responsable legal y operativo',
    `contacto_legal_email` VARCHAR(255) NULL,
    `contacto_legal_telefono` VARCHAR(50) NULL,
    `url_contrato_marco_landonpro` VARCHAR(500) NULL COMMENT 'URL a contrato marco con LandonPro',
    CONSTRAINT `fk_perfil_constructor_contratista_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de perfiles de compañías constructoras / contratistas';

-- -----------------------------------------------------
-- Tabla `propiedades`
-- Representa el inmueble que es el centro de un proyecto de remodelación o construcción.
-- -----------------------------------------------------
CREATE TABLE `propiedades` (
    `id_propiedad` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de la propiedad',
    `direccion` VARCHAR(255) NOT NULL,
    `ciudad` VARCHAR(100) NOT NULL,
    `estado` VARCHAR(100) NULL,
    `codigo_postal` VARCHAR(20) NULL,
    `descripcion_corta` TEXT NULL,
    `metros_cuadrados_construccion` DECIMAL(10, 2) NULL,
    `metros_cuadrados_terreno` DECIMAL(10, 2) NULL,
    `numero_habitaciones` INT NULL,
    `numero_banos` DECIMAL(3, 1) NULL COMMENT 'Permite baños medios (ej. 2.5)',
    `urls_fotos_actuales` JSON NULL COMMENT 'Array de URLs a fotos de la propiedad en su estado actual',
    `urls_planos` JSON NULL COMMENT 'Array de URLs a planos de la propiedad',
    `estado_actual_descripcion` TEXT NULL COMMENT 'Descripción del estado de la propiedad antes del proyecto',
    `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro de la propiedad en la plataforma',
    `id_propietario_registrador` INT NOT NULL COMMENT 'FK a usuarios que registró la propiedad (puede ser General o Constructor)',
    CONSTRAINT `fk_propiedad_propietario` FOREIGN KEY (`id_propietario_registrador`) REFERENCES `usuarios`(`id_usuario`)
    ON DELETE RESTRICT ON UPDATE CASCADE -- Si el propietario se elimina, las propiedades NO se eliminan automáticamente.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de propiedades inmobiliarias';

-- -----------------------------------------------------
-- Tabla `proyectos`
-- La entidad central que agrupa todas las actividades de construcción/remodelación.
-- -----------------------------------------------------
CREATE TABLE `proyectos` (
    `id_proyecto` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del proyecto',
    `id_propiedad` INT NOT NULL UNIQUE COMMENT 'FK a propiedades; una propiedad solo puede tener un proyecto principal activo a la vez.',
    `nombre_proyecto` VARCHAR(255) NOT NULL,
    `id_gerente_proyecto` INT NOT NULL COMMENT 'FK a usuarios (gerente del proyecto, típicamente un ConstructorContratista o Administrador)',
    `descripcion_detallada` TEXT NULL,
    `presupuesto_estimado_total` DECIMAL(15, 2) NULL,
    `roi_estimado_porcentaje` DECIMAL(5, 2) NULL COMMENT 'Retorno de inversión estimado en porcentaje',
    `fecha_inicio_estimada` DATE NULL,
    `fecha_fin_estimada` DATE NULL,
    `fecha_inicio_real` DATE NULL,
    `fecha_fin_real` DATE NULL,
    `estado_proyecto` ENUM('Borrador', 'Propuesta Abierta', 'Financiado', 'En Ejecución', 'En Pausa', 'Completado', 'Cancelado') DEFAULT 'Borrador' NOT NULL,
    CONSTRAINT `fk_proyecto_propiedad` FOREIGN KEY (`id_propiedad`) REFERENCES `propiedades`(`id_propiedad`)
    ON DELETE RESTRICT ON UPDATE CASCADE, -- Un proyecto no debería ser borrado si la propiedad aún existe.
    CONSTRAINT `fk_proyecto_gerente` FOREIGN KEY (`id_gerente_proyecto`) REFERENCES `usuarios`(`id_usuario`)
    ON DELETE RESTRICT ON UPDATE CASCADE -- Un proyecto no debería ser borrado si su gerente aún existe.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de proyectos de construcción/remodelación';

-- -----------------------------------------------------
-- Tabla `fases_proyectos`
-- Define las etapas secuenciales de un Proyecto.
-- -----------------------------------------------------
CREATE TABLE `fases_proyectos` (
    `id_fase` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de la fase',
    `id_proyecto` INT NOT NULL COMMENT 'FK a proyectos',
    `nombre_fase` VARCHAR(255) NOT NULL COMMENT 'Ej: Demolición, Estructura, Acabados Interiores',
    `descripcion` TEXT NULL,
    `fecha_inicio_estimada` DATE NULL,
    `fecha_fin_estimada` DATE NULL,
    `fecha_inicio_real` DATE NULL,
    `fecha_fin_real` DATE NULL,
    `estado_fase` ENUM('Por Iniciar', 'En Progreso', 'Completada', 'Retrasada') DEFAULT 'Por Iniciar' NOT NULL,
    `orden` INT NOT NULL COMMENT 'Número para la secuencia de las fases dentro del proyecto',
    CONSTRAINT `fk_fase_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos`(`id_proyecto`)
    ON DELETE CASCADE ON UPDATE CASCADE -- Eliminar un proyecto elimina todas sus fases.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de fases de proyectos';

-- -----------------------------------------------------
-- Tabla `tareas`
-- Actividades granulares dentro de una Fase de Proyecto.
-- -----------------------------------------------------
CREATE TABLE `tareas` (
    `id_tarea` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de la tarea',
    `id_fase` INT NOT NULL COMMENT 'FK a fases_proyectos',
    `nombre_tarea` VARCHAR(255) NOT NULL,
    `descripcion` TEXT NULL,
    `fecha_vencimiento_estimada` DATE NULL,
    `id_responsable` INT NULL COMMENT 'FK a usuarios (típicamente un Trabajador o ConstructorContratista)',
    `estado_tarea` ENUM('Pendiente', 'En Curso', 'Completada', 'Bloqueada', 'Cancelada') DEFAULT 'Pendiente' NOT NULL,
    `progreso_porcentaje` DECIMAL(5, 2) DEFAULT 0.00 COMMENT 'Porcentaje de progreso de la tarea (0-100%)',
    CONSTRAINT `fk_tarea_fase` FOREIGN KEY (`id_fase`) REFERENCES `fases_proyectos`(`id_fase`)
    ON DELETE CASCADE ON UPDATE CASCADE, -- Eliminar una fase elimina sus tareas.
    CONSTRAINT `fk_tarea_responsable` FOREIGN KEY (`id_responsable`) REFERENCES `usuarios`(`id_usuario`)
    ON DELETE SET NULL ON UPDATE CASCADE -- Si el responsable se elimina, la tarea no se borra, solo queda sin responsable asignado.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de tareas de proyectos';

-- -----------------------------------------------------
-- Tabla `propuestas_inversion`
-- El documento o conjunto de datos que se presenta a los inversores para un Proyecto.
-- -----------------------------------------------------
CREATE TABLE `propuestas_inversion` (
    `id_propuesta` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de la propuesta de inversión',
    `id_proyecto` INT NOT NULL UNIQUE COMMENT 'FK a proyectos; cada proyecto tiene una única propuesta de inversión principal.',
    `titulo_propuesta` VARCHAR(255) NOT NULL,
    `descripcion_financiera` TEXT NULL COMMENT 'Detalles del uso de fondos, proyecciones financieras, etc.',
    `monto_financiacion_requerido` DECIMAL(15, 2) NOT NULL,
    `retorno_inversion_proyectado_porcentaje` DECIMAL(5, 2) NOT NULL,
    `plazo_inversion_meses` INT NULL,
    `url_documento_completo` VARCHAR(500) NULL COMMENT 'URL al PDF o documento completo de la propuesta',
    `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `estado_propuesta` ENUM('Borrador', 'Activa', 'Financiada', 'Cerrada', 'Rechazada') DEFAULT 'Borrador' NOT NULL,
    CONSTRAINT `fk_propuesta_inversion_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos`(`id_proyecto`)
    ON DELETE CASCADE ON UPDATE CASCADE -- Eliminar un proyecto elimina su propuesta de inversión.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de propuestas de inversión para proyectos';

-- -----------------------------------------------------
-- Tabla `inversiones`
-- Registra las aportaciones de capital de los inversores a un Proyecto específico.
-- -----------------------------------------------------
CREATE TABLE `inversiones` (
    `id_inversion` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de la inversión',
    `id_propuesta` INT NOT NULL COMMENT 'FK a propuestas_inversion, la propuesta a la que se aporta capital.',
    `id_inversor` INT NOT NULL COMMENT 'FK a perfiles_inversores, quién realiza la inversión.',
    `monto_invertido` DECIMAL(15, 2) NOT NULL,
    `fecha_inversion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `participacion_porcentaje_proyecto` DECIMAL(5, 2) NULL COMMENT 'Porcentaje de participación o rendimiento específico derivado de esta inversión.',
    `estado_inversion` ENUM('Pendiente', 'Confirmada', 'Reembolsada') DEFAULT 'Pendiente' NOT NULL,
    CONSTRAINT `fk_inversion_propuesta` FOREIGN KEY (`id_propuesta`) REFERENCES `propuestas_inversion`(`id_propuesta`)
    ON DELETE CASCADE ON UPDATE CASCADE, -- Eliminar una propuesta de inversión elimina las inversiones asociadas a ella.
    CONSTRAINT `fk_inversion_inversor` FOREIGN KEY (`id_inversor`) REFERENCES `perfiles_inversores`(`id_perfil_inversor`)
    ON DELETE RESTRICT ON UPDATE CASCADE -- No se debe eliminar una inversión si se elimina el inversor directamente, a menos que se defina otro comportamiento.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de inversiones realizadas por los inversores';

-- -----------------------------------------------------
-- Tabla `documentos_legal_plantillas`
-- Almacena las plantillas de documentos que serán digitalizadas (contratos, acuerdos, etc.).
-- -----------------------------------------------------
CREATE TABLE `documentos_legal_plantillas` (
    `id_plantilla` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de la plantilla de documento',
    `nombre_plantilla` VARCHAR(255) NOT NULL,
    `tipo_documento` ENUM('Contrato Inversion', 'Acuerdo Obra', 'Contrato Servicio', 'Politica Interna', 'Otro') NOT NULL,
    `url_plantilla` VARCHAR(500) NOT NULL COMMENT 'URL al archivo base de la plantilla (PDF, DOCX, etc.)',
    `fecha_subida` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `id_creador` INT NULL COMMENT 'FK a usuarios (quién subió la plantilla, puede ser un administrador)',
    CONSTRAINT `fk_plantilla_creador` FOREIGN KEY (`id_creador`) REFERENCES `usuarios`(`id_usuario`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de plantillas de documentos legales';

-- -----------------------------------------------------
-- Tabla `documentos_instancias`
-- Una copia específica y rellenada de una plantilla de documento, ligada a un Proyecto y con estados de firma.
-- -----------------------------------------------------
CREATE TABLE `documentos_instancias` (
    `id_documento_instancia` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de la instancia del documento',
    `id_plantilla` INT NOT NULL COMMENT 'FK a documentos_legal_plantillas, la plantilla usada.',
    `id_proyecto` INT NOT NULL COMMENT 'FK a proyectos, el proyecto al que se aplica este documento.',
    `nombre_instancia` VARCHAR(255) NOT NULL COMMENT 'Título del documento generado (ej., "Contrato de Inversión - Proyecto X - Inversor Y")',
    `url_documento_generado` VARCHAR(500) NULL COMMENT 'URL al documento final con datos y/o firmas',
    `firmantes_info` JSON NULL COMMENT 'JSON con IDs de usuarios y estado de su firma (ej. {"1": "firmado", "5": "pendiente"})',
    `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `fecha_firma` DATETIME NULL COMMENT 'Fecha de la última firma o de finalización de firmas',
    `estado_firma` ENUM('Borrador', 'Pendiente Firma', 'Firmado', 'Anulado') DEFAULT 'Borrador' NOT NULL,
    CONSTRAINT `fk_documento_instancia_plantilla` FOREIGN KEY (`id_plantilla`) REFERENCES `documentos_legal_plantillas`(`id_plantilla`)
    ON DELETE RESTRICT ON UPDATE CASCADE, -- La instancia no se borra si se borra la plantilla.
    CONSTRAINT `fk_documento_instancia_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos`(`id_proyecto`)
    ON DELETE CASCADE ON UPDATE CASCADE -- Si el proyecto se borra, se borran sus documentos de instancia.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de instancias de documentos generados y firmados';

-- -----------------------------------------------------
-- Tabla `mensajes_chat`
-- Para la comunicación interna dentro de un proyecto.
-- -----------------------------------------------------
CREATE TABLE `mensajes_chat` (
    `id_mensaje` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del mensaje',
    `id_proyecto` INT NOT NULL COMMENT 'FK a proyectos, el proyecto al que pertenece el chat.',
    `id_emisor` INT NOT NULL COMMENT 'FK a usuarios, quién envió el mensaje.',
    `contenido` TEXT NOT NULL,
    `fecha_envio` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `id_mensaje_padre` INT NULL COMMENT 'FK recursiva a otro mensaje_chat para hilos de conversación',
    `leido_por` JSON NULL COMMENT 'Array de IDs de usuarios que han leído el mensaje',
    CONSTRAINT `fk_mensaje_chat_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos`(`id_proyecto`)
    ON DELETE CASCADE ON UPDATE CASCADE, -- Si el proyecto se borra, se borran sus mensajes de chat.
    CONSTRAINT `fk_mensaje_chat_emisor` FOREIGN KEY (`id_emisor`) REFERENCES `usuarios`(`id_usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE, -- Si el emisor se borra, sus mensajes se borran.
    CONSTRAINT `fk_mensaje_chat_padre` FOREIGN KEY (`id_mensaje_padre`) REFERENCES `mensajes_chat`(`id_mensaje`)
    ON DELETE SET NULL ON UPDATE CASCADE -- Si un mensaje padre se borra, el hijo no se borra, solo pierde su referencia.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de mensajes de chat por proyecto';

-- -----------------------------------------------------
-- Tabla `publicaciones`
-- Entidad para las noticias o publicaciones tipo blog.
-- -----------------------------------------------------
CREATE TABLE `publicaciones` (
    `id_publicacion` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de la publicación',
    `id_autor` INT NOT NULL COMMENT 'FK a usuarios, quién realizó la publicación.',
    `titulo` VARCHAR(255) NOT NULL,
    `contenido_html` LONGTEXT NOT NULL COMMENT 'El cuerpo de la publicación (puede ser HTML o Markdown).',
    `fecha_publicacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `estado_publicacion` ENUM('Borrador', 'Publicado', 'Archivado') DEFAULT 'Borrador' NOT NULL,
    `url_imagen_principal` VARCHAR(500) NULL COMMENT 'URL a la imagen destacada de la publicación (opcional).',
    CONSTRAINT `fk_publicacion_autor` FOREIGN KEY (`id_autor`) REFERENCES `usuarios`(`id_usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE -- Si el autor se elimina, sus publicaciones también.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de noticias o publicaciones tipo blog';

-- -----------------------------------------------------
-- Tabla `comentarios_publicaciones`
-- Los comentarios que los usuarios pueden hacer en las publicaciones.
-- -----------------------------------------------------
CREATE TABLE `comentarios_publicaciones` (
    `id_comentario` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del comentario',
    `id_publicacion` INT NOT NULL COMMENT 'FK a publicaciones',
    `id_autor` INT NOT NULL COMMENT 'FK a usuarios',
    `contenido` TEXT NOT NULL,
    `fecha_comentario` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `id_comentario_padre` INT NULL COMMENT 'FK recursiva a otro comentario_publicacion para anidación de comentarios',
    CONSTRAINT `fk_comentario_publicacion` FOREIGN KEY (`id_publicacion`) REFERENCES `publicaciones`(`id_publicacion`)
    ON DELETE CASCADE ON UPDATE CASCADE, -- Si la publicación se borra, se borran sus comentarios.
    CONSTRAINT `fk_comentario_autor` FOREIGN KEY (`id_autor`) REFERENCES `usuarios`(`id_usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE, -- Si el autor se elimina, sus comentarios se eliminan.
    CONSTRAINT `fk_comentario_padre` FOREIGN KEY (`id_comentario_padre`) REFERENCES `comentarios_publicaciones`(`id_comentario`)
    ON DELETE SET NULL ON UPDATE CASCADE -- Si un comentario padre se borra, el hijo no se borra, solo pierde su referencia.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de comentarios en publicaciones';

-- -----------------------------------------------------
-- Tabla `alertas`
-- Notificaciones automáticas para los usuarios sobre eventos importantes.
-- -----------------------------------------------------
CREATE TABLE `alertas` (
    `id_alerta` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de la alerta',
    `id_usuario` INT NOT NULL COMMENT 'FK a usuarios, el destinatario de la alerta.',
    `tipo_alerta` ENUM('Costo Excedido', 'Retraso Tarea', 'Nueva Inversion', 'Documento Pendiente', 'Mensaje Nuevo', 'Cambio Estado Proyecto', 'Otro') NOT NULL,
    `mensaje` VARCHAR(500) NOT NULL,
    `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `id_entidad_referencia` INT NULL COMMENT 'ID de la entidad relacionada (Proyecto, Tarea, etc.)',
    `tipo_entidad_referencia` ENUM('Proyecto', 'Tarea', 'PropuestaInversion', 'DocumentoInstancia', 'MensajeChat', 'Publicacion', 'Inversion') NULL COMMENT 'Tipo de entidad a la que hace referencia id_entidad_referencia (para referencia polimórfica)',
    `leida` BOOLEAN DEFAULT FALSE,
    CONSTRAINT `fk_alerta_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE -- Si el usuario se elimina, sus alertas se eliminan.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de notificaciones automáticas y alertas';

-- -----------------------------------------------------
-- Tabla `participantes_proyecto`
-- Tabla de unión para manejar la relación Muchos a Muchos entre Proyecto y Usuario,
-- permitiendo asignar roles específicos a usuarios dentro de un proyecto.
-- -----------------------------------------------------
CREATE TABLE `participantes_proyecto` (
    `id_participante_proyecto` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del participante en el proyecto',
    `id_proyecto` INT NOT NULL COMMENT 'FK a proyectos',
    `id_usuario` INT NOT NULL COMMENT 'FK a usuarios',
    `rol_en_proyecto` ENUM('Gerente', 'Inversor', 'Constructor Principal', 'Subcontratista', 'Arquitecto', 'Trabajador', 'Auditor', 'Cliente Interesado', 'Otro') NOT NULL,
    `fecha_asignacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `estado_participacion` ENUM('Activo', 'Inactivo', 'Completado') DEFAULT 'Activo' NOT NULL,
    -- Restricción única para evitar que un usuario tenga el mismo rol múltiples veces en el mismo proyecto.
    UNIQUE (`id_proyecto`, `id_usuario`, `rol_en_proyecto`),
    CONSTRAINT `fk_participante_proyecto_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos`(`id_proyecto`)
    ON DELETE CASCADE ON UPDATE CASCADE, -- Si el proyecto se elimina, los registros de participación también.
    CONSTRAINT `fk_participante_proyecto_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE -- Si el usuario se elimina, sus participaciones en proyectos también.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT 'Tabla de participantes y sus roles en proyectos';

-- -----------------------------------------------------
-- Índices opcionales para mejorar el rendimiento de las consultas frecuentes.
-- -----------------------------------------------------
CREATE INDEX `idx_proyecto_estado` ON `proyectos` (`estado_proyecto`);
CREATE INDEX `idx_tarea_estado` ON `tareas` (`estado_tarea`);
CREATE INDEX `idx_inversion_estado` ON `inversiones` (`estado_inversion`);
CREATE INDEX `idx_usuario_tipo_perfil` ON `usuarios` (`tipo_perfil`);
CREATE INDEX `idx_publicacion_fecha` ON `publicaciones` (`fecha_publicacion`);
CREATE INDEX `idx_alerta_leida` ON `alertas` (`leida`);