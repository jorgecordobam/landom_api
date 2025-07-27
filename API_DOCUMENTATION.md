# Documentación de la API - LandonPro

## Base URL
```
http://localhost:8000/api
```

## Autenticación
La API utiliza Laravel Sanctum para autenticación. Los tokens se envían en el header:
```
Authorization: Bearer {token}
```

## Endpoints

### 🔐 Autenticación

#### Registro de Usuario
```http
POST /auth/register
```

**Body:**
```json
{
    "nombre": "Juan",
    "apellido": "Pérez",
    "email": "juan@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "telefono": "+1234567890",
    "tipo_perfil": "Inversor"
}
```

**Respuesta:**
```json
{
    "message": "Usuario registrado exitosamente",
    "user": {
        "id": 1,
        "nombre": "Juan",
        "apellido": "Pérez",
        "email": "juan@example.com",
        "tipo_perfil": "Inversor",
        "estado_verificacion": "Pendiente"
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
}
```

#### Login
```http
POST /auth/login
```

**Body:**
```json
{
    "email": "juan@example.com",
    "password": "password123"
}
```

#### Logout
```http
POST /auth/logout
```
*Requiere autenticación*

#### Obtener Usuario Actual
```http
GET /auth/me
```
*Requiere autenticación*

#### Actualizar Perfil
```http
PUT /auth/profile
```
*Requiere autenticación*

**Body:**
```json
{
    "nombre": "Juan Carlos",
    "apellido": "Pérez",
    "telefono": "+1234567890"
}
```

#### Cambiar Contraseña
```http
PUT /auth/password/change
```
*Requiere autenticación*

**Body:**
```json
{
    "current_password": "password123",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

#### Enviar Link de Reseteo
```http
POST /auth/password/send-reset-link
```

**Body:**
```json
{
    "email": "juan@example.com"
}
```

#### Resetear Contraseña
```http
POST /auth/password/reset
```

**Body:**
```json
{
    "email": "juan@example.com",
    "token": "reset_token_here",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

### 🏠 Propiedades

#### Listar Propiedades
```http
GET /properties
```

**Query Parameters:**
- `user_id` - Filtrar por propietario
- `ciudad` - Filtrar por ciudad
- `estado` - Filtrar por estado

#### Crear Propiedad
```http
POST /properties
```
*Requiere autenticación*

**Body:**
```json
{
    "direccion": "123 Main St",
    "ciudad": "Miami",
    "estado": "FL",
    "codigo_postal": "33101",
    "descripcion_corta": "Casa de 3 habitaciones",
    "metros_cuadrados_construccion": 150.5,
    "metros_cuadrados_terreno": 200.0,
    "numero_habitaciones": 3,
    "numero_banos": 2.5,
    "urls_fotos_actuales": ["url1", "url2"],
    "urls_planos": ["plano1", "plano2"],
    "estado_actual_descripcion": "Casa en buen estado"
}
```

#### Mis Propiedades
```http
GET /properties/my-properties
```
*Requiere autenticación*

#### Ver Propiedad
```http
GET /properties/{id}
```

#### Actualizar Propiedad
```http
PUT /properties/{id}
```
*Requiere autenticación (propietario o admin)*

#### Eliminar Propiedad
```http
DELETE /properties/{id}
```
*Requiere autenticación (propietario o admin)*

### 🏗️ Proyectos

#### Listar Proyectos
```http
GET /projects
```

**Query Parameters:**
- `estado_proyecto` - Filtrar por estado
- `gerente_id` - Filtrar por gerente
- `propiedad_id` - Filtrar por propiedad

#### Crear Proyecto
```http
POST /projects
```
*Requiere autenticación*

**Body:**
```json
{
    "id_propiedad": 1,
    "nombre_proyecto": "Remodelación Casa Miami",
    "descripcion_detallada": "Remodelación completa de casa",
    "presupuesto_estimado_total": 50000.00,
    "roi_estimado_porcentaje": 15.5,
    "fecha_inicio_estimada": "2024-01-15",
    "fecha_fin_estimada": "2024-06-15"
}
```

#### Mis Proyectos
```http
GET /projects/my-projects
```
*Requiere autenticación*

#### Proyectos Participados
```http
GET /projects/participated
```
*Requiere autenticación*

#### Ver Proyecto
```http
GET /projects/{id}
```

#### Actualizar Proyecto
```http
PUT /projects/{id}
```
*Requiere autenticación (gerente o admin)*

#### Eliminar Proyecto
```http
DELETE /projects/{id}
```
*Requiere autenticación (gerente o admin)*

#### Actualizar Estado del Proyecto
```http
PUT /projects/{id}/status
```
*Requiere autenticación (gerente o admin)*

**Body:**
```json
{
    "estado_proyecto": "En Ejecución"
}
```

### 💰 Inversiones

#### Listar Inversiones
```http
GET /investor/investments
```
*Requiere autenticación*

**Query Parameters:**
- `inversor_id` - Filtrar por inversor
- `estado_inversion` - Filtrar por estado

#### Realizar Inversión
```http
POST /investor/investments
```
*Requiere autenticación (inversor)*

**Body:**
```json
{
    "id_propuesta": 1,
    "monto_invertido": 10000.00,
    "participacion_porcentaje_proyecto": 20.0
}
```

#### Oportunidades de Inversión
```http
GET /investor/investments/opportunities
```

**Query Parameters:**
- `roi_min` - ROI mínimo
- `roi_max` - ROI máximo
- `monto_min` - Monto mínimo
- `monto_max` - Monto máximo

#### Mis Inversiones
```http
GET /investor/investments/my-investments
```
*Requiere autenticación (inversor)*

#### Estadísticas de Inversión
```http
GET /investor/investments/statistics
```
*Requiere autenticación (inversor)*

#### Ver Inversión
```http
GET /investor/investments/{id}
```

#### Actualizar Inversión
```http
PUT /investor/investments/{id}
```
*Requiere autenticación (propietario o admin)*

### 👥 Perfil de Usuario

#### Ver Perfil
```http
GET /profile
```
*Requiere autenticación*

#### Actualizar Perfil
```http
PUT /profile
```
*Requiere autenticación*

#### Actualizar Avatar
```http
POST /profile/avatar
```
*Requiere autenticación*

#### Cambiar Contraseña
```http
PUT /profile/password
```
*Requiere autenticación*

### 📄 Documentos

#### Mis Documentos
```http
GET /profile/documents
```
*Requiere autenticación*

#### Subir Documento
```http
POST /profile/documents
```
*Requiere autenticación*

#### Ver Documento
```http
GET /profile/documents/{id}
```
*Requiere autenticación*

#### Actualizar Documento
```http
PUT /profile/documents/{id}
```
*Requiere autenticación*

#### Eliminar Documento
```http
DELETE /profile/documents/{id}
```
*Requiere autenticación*

### 📊 Mis Inversiones (Perfil)

#### Listar Mis Inversiones
```http
GET /profile/investments
```
*Requiere autenticación (inversor)*

#### Estadísticas de Inversión
```http
GET /profile/investments/statistics
```
*Requiere autenticación (inversor)*

#### Historial de Inversiones
```http
GET /profile/investments/history
```
*Requiere autenticación (inversor)*

#### Ver Inversión
```http
GET /profile/investments/{id}
```
*Requiere autenticación (inversor)*

### 🏢 Administración (Solo Admin)

#### Gestión de Usuarios
```http
GET /admin/users
PUT /admin/users/{id}/status
DELETE /admin/users/{id}
```

#### Verificación de Empresas
```http
GET /admin/companies/pending
POST /admin/companies/{id}/verify
POST /admin/companies/{id}/reject
```

#### Supervisión de Proyectos
```http
GET /admin/projects
GET /admin/projects/statistics
PUT /admin/projects/{id}/status
```

#### Configuración del Sistema
```http
GET /admin/settings
PUT /admin/settings
GET /admin/settings/templates
PUT /admin/settings/templates/{id}
```

### 🏗️ Tareas de Proyecto

#### Listar Tareas
```http
GET /projects/{projectId}/tasks
```

#### Crear Tarea
```http
POST /projects/{projectId}/tasks
```

#### Ver Tarea
```http
GET /projects/{projectId}/tasks/{id}
```

#### Actualizar Tarea
```http
PUT /projects/{projectId}/tasks/{id}
```

#### Eliminar Tarea
```http
DELETE /projects/{projectId}/tasks/{id}
```

#### Actualizar Estado de Tarea
```http
PUT /projects/{projectId}/tasks/{id}/status
```

### 📱 Media de Proyecto

#### Listar Media
```http
GET /projects/{projectId}/media
```

#### Subir Media
```http
POST /projects/{projectId}/media
```

#### Ver Media
```http
GET /projects/{projectId}/media/{id}
```

#### Actualizar Media
```http
PUT /projects/{projectId}/media/{id}
```

#### Eliminar Media
```http
DELETE /projects/{projectId}/media/{id}
```

#### Media por Tipo
```http
GET /projects/{projectId}/media/type/{type}
```

### 👷 Trabajadores de Proyecto

#### Listar Trabajadores
```http
GET /projects/{projectId}/workers
```

#### Asignar Trabajador
```http
POST /projects/{projectId}/workers
```

#### Actualizar Trabajador
```http
PUT /projects/{projectId}/workers/{workerId}
```

#### Eliminar Trabajador
```http
DELETE /projects/{projectId}/workers/{workerId}
```

#### Rendimiento del Trabajador
```http
GET /projects/{projectId}/workers/{workerId}/performance
```

### 🏢 Listados de Proyectos (Inversores)

#### Listar Proyectos
```http
GET /investor/projects
```

#### Proyectos Destacados
```http
GET /investor/projects/featured
```

#### Buscar Proyectos
```http
GET /investor/projects/search
```

#### Filtrar Proyectos
```http
POST /investor/projects/filter
```

#### Ver Proyecto
```http
GET /investor/projects/{id}
```

### 💬 Comunidad

#### Listar Publicaciones
```http
GET /community/posts
```

#### Crear Publicación
```http
POST /community/posts
```

#### Ver Publicación
```http
GET /community/posts/{id}
```

#### Actualizar Publicación
```http
PUT /community/posts/{id}
```

#### Eliminar Publicación
```http
DELETE /community/posts/{id}
```

#### Me Gusta Publicación
```http
POST /community/posts/{id}/toggle-like
```

### 💬 Comentarios

#### Listar Comentarios
```http
GET /community/posts/{postId}/comments
```

#### Crear Comentario
```http
POST /community/posts/{postId}/comments
```

#### Ver Comentario
```http
GET /community/posts/{postId}/comments/{id}
```

#### Actualizar Comentario
```http
PUT /community/posts/{postId}/comments/{id}
```

#### Eliminar Comentario
```http
DELETE /community/posts/{postId}/comments/{id}
```

#### Me Gusta Comentario
```http
POST /community/posts/{postId}/comments/{id}/toggle-like
```

### 💬 Chat

#### Salas de Chat
```http
GET /chat/rooms
POST /chat/rooms
GET /chat/rooms/{id}
PUT /chat/rooms/{id}
POST /chat/rooms/{id}/add-user
POST /chat/rooms/{id}/remove-user
```

#### Mensajes de Chat
```http
GET /chat/rooms/{chatRoomId}/messages
POST /chat/rooms/{chatRoomId}/messages
GET /chat/rooms/{chatRoomId}/messages/{id}
PUT /chat/rooms/{chatRoomId}/messages/{id}
DELETE /chat/rooms/{chatRoomId}/messages/{id}
POST /chat/rooms/{chatRoomId}/messages/{id}/mark-read
```

## Códigos de Respuesta

- `200` - OK
- `201` - Creado
- `400` - Bad Request
- `401` - No autorizado
- `403` - Prohibido
- `404` - No encontrado
- `422` - Error de validación
- `500` - Error interno del servidor

## Ejemplos de Uso

### Registro e Inicio de Sesión
```bash
# 1. Registrar usuario
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan",
    "apellido": "Pérez",
    "email": "juan@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "tipo_perfil": "Inversor"
  }'

# 2. Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "juan@example.com",
    "password": "password123"
  }'
```

### Crear Propiedad y Proyecto
```bash
# 1. Crear propiedad
curl -X POST http://localhost:8000/api/properties \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "direccion": "123 Main St",
    "ciudad": "Miami",
    "estado": "FL",
    "descripcion_corta": "Casa de 3 habitaciones"
  }'

# 2. Crear proyecto
curl -X POST http://localhost:8000/api/projects \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "id_propiedad": 1,
    "nombre_proyecto": "Remodelación Casa Miami",
    "presupuesto_estimado_total": 50000.00,
    "roi_estimado_porcentaje": 15.5
  }'
```

### Realizar Inversión
```bash
curl -X POST http://localhost:8000/api/investor/investments \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "id_propuesta": 1,
    "monto_invertido": 10000.00,
    "participacion_porcentaje_proyecto": 20.0
  }'
```

## Notas Importantes

1. **Autenticación**: Todos los endpoints protegidos requieren el header `Authorization: Bearer {token}`
2. **Validación**: Los datos se validan automáticamente según las reglas definidas
3. **Paginación**: Los endpoints de listado incluyen paginación automática
4. **Permisos**: Algunos endpoints requieren roles específicos (admin, inversor, etc.)
5. **Archivos**: Para subir archivos, usar `multipart/form-data` en lugar de JSON 