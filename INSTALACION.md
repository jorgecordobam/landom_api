# Instrucciones de Instalación - LandonPro

## Requisitos Previos

Asegúrate de tener instalados los siguientes componentes en tu sistema:

- **PHP**: Versión 8.1 o superior
- **Composer**: Gestor de dependencias de PHP
- **MySQL**: Versión 5.7 o superior
- **Node.js y npm/yarn**: Para la compilación de assets
- **Git**: Para clonar el repositorio

## Pasos de Instalación

### 1. Clonar el Repositorio

```bash
git clone <url-del-repositorio>
cd landonPro_api
```

### 2. Instalar Dependencias de PHP

```bash
composer install
```

### 3. Configurar Variables de Entorno

Copia el archivo de ejemplo y configura las variables:

```bash
cp .env.example .env
```

Edita el archivo `.env` con la configuración de tu base de datos:

```env
APP_NAME=LandonPro
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=landonpro_db
DB_USERNAME=root
DB_PASSWORD=tu_contraseña_aqui
```

### 4. Generar Clave de Aplicación

```bash
php artisan key:generate
```

### 5. Crear la Base de Datos

Crea la base de datos MySQL:

```sql
CREATE DATABASE landonpro_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Ejecutar Migraciones

```bash
php artisan migrate
```

### 7. Ejecutar Seeders (Opcional)

```bash
php artisan db:seed
```

### 8. Instalar Dependencias de Node.js (si usas Vite/Mix)

```bash
npm install
npm run dev
```

### 9. Configurar Almacenamiento

```bash
php artisan storage:link
```

### 10. Configurar Permisos (Linux/Mac)

```bash
chmod -R 775 storage bootstrap/cache
```

## Estructura de la Base de Datos

La aplicación incluye las siguientes tablas principales:

### Tablas de Usuarios y Perfiles
- `usuarios` - Información base de todos los usuarios
- `perfiles_inversores` - Perfiles específicos de inversores
- `perfiles_trabajadores` - Perfiles de trabajadores y técnicos
- `perfiles_constructores_contratistas` - Perfiles de empresas constructoras

### Tablas de Propiedades y Proyectos
- `propiedades` - Propiedades inmobiliarias
- `proyectos` - Proyectos de construcción/remodelación
- `fases_proyectos` - Fases de los proyectos
- `tareas` - Tareas específicas dentro de las fases

### Tablas de Inversión
- `propuestas_inversion` - Propuestas de inversión para proyectos
- `inversiones` - Inversiones realizadas por los inversores

### Tablas de Documentación
- `documentos_legal_plantillas` - Plantillas de documentos legales
- `documentos_instancias` - Instancias de documentos firmados

### Tablas de Comunicación
- `mensajes_chat` - Mensajes de chat por proyecto
- `publicaciones` - Publicaciones tipo blog
- `comentarios_publicaciones` - Comentarios en publicaciones
- `alertas` - Notificaciones automáticas

### Tablas de Relaciones
- `participantes_proyecto` - Participantes y roles en proyectos

## Modelos de Laravel

La aplicación incluye los siguientes modelos principales:

### Modelos de Usuario
- `User` - Usuario base
- `PerfilInversor` - Perfil de inversor
- `PerfilTrabajador` - Perfil de trabajador
- `PerfilConstructorContratista` - Perfil de contratista

### Modelos de Proyecto
- `Project` - Proyecto principal
- `Propiedad` - Propiedad inmobiliaria
- `FaseProyecto` - Fases del proyecto
- `Task` - Tareas del proyecto

### Modelos de Inversión
- `PropuestaInversion` - Propuesta de inversión
- `Inversion` - Inversión realizada

### Modelos de Documentación
- `DocumentoLegalPlantilla` - Plantilla de documento
- `DocumentoInstancia` - Instancia de documento

### Modelos de Comunicación
- `MensajeChat` - Mensaje de chat
- `Publicacion` - Publicación
- `ComentarioPublicacion` - Comentario
- `Alerta` - Alerta/notificación

### Modelos de Relaciones
- `ParticipanteProyecto` - Participante en proyecto

## Verificación de la Instalación

### 1. Verificar Migraciones

```bash
php artisan migrate:status
```

### 2. Verificar Rutas

```bash
php artisan route:list
```

### 3. Probar la Aplicación

Inicia el servidor de desarrollo:

```bash
php artisan serve
```

Visita `http://localhost:8000` en tu navegador.

## Comandos Útiles

### Limpiar Caché
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Revertir Migraciones
```bash
php artisan migrate:rollback
```

### Recrear Base de Datos
```bash
php artisan migrate:fresh --seed
```

### Verificar Sintaxis
```bash
php artisan route:list
php artisan config:cache
```

## Solución de Problemas

### Error de Conexión a Base de Datos
- Verifica que MySQL esté ejecutándose
- Confirma las credenciales en `.env`
- Asegúrate de que la base de datos existe

### Error de Permisos
- En Linux/Mac: `chmod -R 775 storage bootstrap/cache`
- En Windows: Asegúrate de que el usuario tenga permisos de escritura

### Error de Composer
- Ejecuta `composer install --ignore-platform-reqs` si hay problemas de versiones

### Error de Migraciones
- Verifica que todas las migraciones estén en el orden correcto
- Ejecuta `php artisan migrate:fresh` para recrear la base de datos

## Configuración de Producción

Para producción, asegúrate de:

1. Cambiar `APP_ENV=production`
2. Cambiar `APP_DEBUG=false`
3. Configurar una base de datos de producción
4. Configurar el almacenamiento de archivos
5. Configurar el envío de correos
6. Configurar HTTPS
7. Optimizar la aplicación: `php artisan config:cache`

## Soporte

Para soporte técnico, contacta al equipo de desarrollo o consulta la documentación del proyecto. 