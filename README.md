# TOTS Backend - API RESTful para Gestión de Espacios y Reservas

API RESTful desarrollada con Laravel 10 para la gestión de espacios y reservas, con autenticación JWT y documentación Swagger integrada.


<img align="center" width=950 src="https://i.postimg.cc/prDjbt4J/image.png">

## Características

- Autenticación JWT (registro e inicio de sesión)
- Modulo de usuarios (solo administradores)
- Modulo de espacios (solo administradores pueden crear/editar/eliminar)
- Modulo completo de reservas (usuarios solo pueden gestionar sus propias reservas)
- Documentación Swagger/OpenAPI integrada
- Suite completa de tests
- Seeders para datos de prueba y administradores

## Requisitos Previos

- PHP >= 8.1
- Composer
- MySQL/PostgreSQL/SQLite
- Laravel 10.x

## Instalación

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd tots-backend
```

2. **Instalar dependencias**
```bash
composer install
```

3. **Configurar el archivo .env**
```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env` con tus credenciales de base de datos:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tots_backend
DB_USERNAME=root
DB_PASSWORD=
```

4. **Instalar JWT**
```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

5. **Ejecutar migraciones y seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **Generar documentación Swagger**
```bash
php artisan l5-swagger:generate
```

## Documentación Swagger

La documentación completa de la API está disponible en:

```
http://localhost:8000/api/documentation
```

Para regenerar la documentación después de cambios:

```bash
php artisan l5-swagger:generate
```

## Tests

Ejecutar la suite de tests:

```bash
php artisan test
```


## Usuarios por Defecto

Después de ejecutar los seeders, tendrás los siguientes usuarios:

- **Administrador:**
  - Email: `admin@example.com`
  - Password: `admin123`
  - Rol: `admin`

- **Usuarios de Prueba:**
  - Email: `user1@example.com` / Password: `password123`
  - Email: `user2@example.com` / Password: `password123`
  - Rol: `user`

## Estructura de la API

### Autenticación

#### Registro
```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "juan@example.com",
  "password": "password123"
}
```

Respuesta:
```json
{
  "status": "success",
  "message": "Login exitoso",
  "data": {
    "user": {
      "id": 1,
      "name": "Juan Pérez",
      "email": "juan@example.com",
      "role": "user"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
```

#### Obtener Usuario Autenticado
```http
GET /api/auth/me
Authorization: Bearer {token}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

### Usuarios (Solo Administradores)

- `GET /api/users` - Listar todos los usuarios
- `POST /api/users` - Crear usuario
- `GET /api/users/{id}` - Ver usuario específico
- `PUT /api/users/{id}` - Actualizar usuario
- `DELETE /api/users/{id}` - Eliminar usuario

### Espacios

- `GET /api/spaces` - Listar espacios (todos los usuarios autenticados)
- `POST /api/spaces` - Crear espacio (solo admin)
- `GET /api/spaces/{id}` - Ver espacio específico
- `PUT /api/spaces/{id}` - Actualizar espacio (solo admin)
- `DELETE /api/spaces/{id}` - Eliminar espacio (solo admin)

**Filtros disponibles:**
- `?is_active=true` - Filtrar por espacios activos

### Reservas

- `GET /api/reservations` - Listar reservas del usuario autenticado
- `GET /api/reservations/calendar` - Obtener reservas para calendario
- `POST /api/reservations` - Crear reserva
- `GET /api/reservations/{id}` - Ver reserva específica
- `PUT /api/reservations/{id}` - Actualizar reserva (solo propia)
- `DELETE /api/reservations/{id}` - Eliminar reserva (solo propia)

**Filtros para listar reservas:**
- `?space_id=1` - Filtrar por espacio
- `?start_date=2024-01-15` - Fecha de inicio
- `?end_date=2024-01-20` - Fecha de fin
- `?status=confirmed` - Filtrar por estado (pending, confirmed, cancelled)

**Endpoint de Calendario:**
```http
GET /api/reservations/calendar?start_date=2024-01-15&end_date=2024-01-20&space_id=1
Authorization: Bearer {token}
```

Este endpoint está optimizado para construir calendarios y retorna todas las reservas confirmadas en el rango de fechas especificado.

## Estructura de Base de Datos

### Tabla: users
- id
- name
- email
- password
- role (enum: 'user', 'admin')
- timestamps

### Tabla: spaces
- id
- name
- description
- capacity
- location
- is_active
- timestamps

### Tabla: reservations
- id
- user_id (foreign key)
- space_id (foreign key)
- title
- description
- start_time
- end_time
- status (enum: 'pending', 'confirmed', 'cancelled')
- timestamps

## Desarrollo

### Ejecutar servidor de desarrollo

```bash
php artisan serve
```

### Limpiar caché

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Seguridad

- Todas las contraseñas se almacenan con hash bcrypt
- Los tokens JWT tienen expiración configurable
- Middleware de autenticación en todas las rutas protegidas
- Validación de entrada en todos los endpoints
- Protección CSRF para rutas web

## Licencia

Este proyecto está bajo la Licencia MIT.

## Notas Adicionales

- Las reservas deben crearse con fechas futuras
- El sistema valida que la fecha de fin sea posterior a la fecha de inicio
- Los espacios inactivos no pueden ser reservados
- Las reservas canceladas no bloquean horarios para nuevas reservas
