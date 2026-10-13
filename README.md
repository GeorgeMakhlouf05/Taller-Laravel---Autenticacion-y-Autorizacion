# EventHub Solutions — API de Autenticación y Autorización

API RESTful desarrollada con Laravel para la gestión de eventos y conferencias tecnológicas de EventHub Solutions. Forma parte del **Taller Autenticación y Autorización** (UNAB, Desarrollo Backend).

## Objetivos

- Desacoplar el cliente (web y móvil) de la lógica de negocio mediante una API REST que responde siempre en JSON.
- Autenticar usuarios sin sesiones ni cookies, usando tokens Bearer emitidos con **Laravel Sanctum**.
- Proteger la propiedad de los eventos con una **Policy** (`EventoPolicy`): solo el organizador propietario puede modificar su evento.
- Restringir las métricas globales al rol administrador con un **Gate** (`ver-finanzas`) registrado en `AppServiceProvider`.

## Requisitos previos

- PHP 8.3 o superior (con las extensiones `sqlite3`, `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`)
- Composer
- Git
- Un cliente HTTP para probar la API (Thunder Client, Postman o similar)

El proyecto usa SQLite, por lo que no requiere instalar un servidor de base de datos.

## Instalación

```powershell
git clone <URL-DEL-REPOSITORIO>
cd eventhub-api
composer install
copy .env.example .env
php artisan key:generate
```

## Configuración de la base de datos

El archivo `.env` ya apunta a SQLite:

```
DB_CONNECTION=sqlite
```

Si el archivo `database/database.sqlite` no existe, créalo (en Windows PowerShell):

```powershell
New-Item database\database.sqlite -ItemType File
```

Si ejecutas `php artisan migrate` sin ese archivo, Laravel también te ofrecerá crearlo.

## Migraciones y datos de prueba

```powershell
php artisan migrate:fresh --seed
```

Este comando crea las tablas `users` y `eventos` (además de `personal_access_tokens` de Sanctum) y ejecuta el seeder. El seeder es repetible: puede ejecutarse varias veces sin duplicar usuarios ni eventos.

## Iniciar el servidor

```powershell
php artisan serve
```

La API queda disponible en `http://127.0.0.1:8000`. Todas las rutas tienen el prefijo `/api`.

## Cuentas de prueba (solo para desarrollo local)

La contraseña de todas las cuentas es `password`.

| Rol | Correo |
|---|---|
| admin | admin@eventhub.com |
| organizador (Organizador A) | organizador.a@eventhub.com |
| organizador (Organizador B) | organizador.b@eventhub.com |
| asistente | asistente@eventhub.com |

El evento de prueba (`id` 1) pertenece al Organizador A.

## Cabeceras

En todas las peticiones se recomienda enviar:

```
Accept: application/json
```

En los endpoints protegidos se debe enviar el token recibido en el login:

```
Authorization: Bearer 1|abc123...
```

### Ejemplos con curl

En PowerShell se debe escribir `curl.exe` (y no `curl`, que es un alias de otro comando).

Iniciar sesión:

```powershell
curl.exe -X POST http://127.0.0.1:8000/api/login -H "Accept: application/json" -H "Content-Type: application/json" -d "{\"email\":\"admin@eventhub.com\",\"password\":\"password\"}"
```

Consultar el perfil con el token recibido (reemplaza el texto después de `Bearer` por tu token completo, sin comillas):

```powershell
curl.exe http://127.0.0.1:8000/api/perfil -H "Accept: application/json" -H "Authorization: Bearer 1|TU_TOKEN_AQUI"
```

## Endpoints

| Método | Ruta | Autenticación | Descripción |
|---|---|---|---|
| POST | `/api/login` | No | Inicia sesión y devuelve un token Bearer |
| GET | `/api/perfil` | Sí (Sanctum) | Datos del usuario autenticado |
| GET | `/api/eventos` | No | Catálogo público de eventos |
| PUT | `/api/eventos/{id}` | Sí (Sanctum + Policy) | Actualiza un evento propio |
| GET | `/api/admin/metricas` | Sí (Sanctum + Gate) | Métricas globales (solo admin) |

### POST /api/login

Cuerpo:

```json
{ "email": "admin@eventhub.com", "password": "password" }
```

Respuesta `200 OK`:

```json
{
  "message": "Inicio de sesión exitoso.",
  "token": "1|abc123...",
  "token_type": "Bearer"
}
```

Credenciales incorrectas, `401 Unauthorized`:

```json
{ "message": "Credenciales incorrectas." }
```

Datos inválidos (por ejemplo, cuerpo vacío), `422 Unprocessable Content`:

```json
{
  "message": "El correo electrónico es obligatorio. (and 1 more error)",
  "errors": {
    "email": ["El correo electrónico es obligatorio."],
    "password": ["La contraseña es obligatoria."]
  }
}
```

### GET /api/perfil

Requiere `Authorization: Bearer <token>`. Respuesta `200 OK`:

```json
{
  "id": 1,
  "name": "Administrador Global",
  "email": "admin@eventhub.com",
  "role": "admin"
}
```

Sin token o con token inválido, `401 Unauthorized`:

```json
{ "message": "No autenticado. Se requiere un token válido." }
```

### GET /api/eventos

No requiere autenticación. Respuesta `200 OK`:

```json
[
  {
    "id": 1,
    "titulo": "Conferencia de Desarrollo Backend",
    "descripcion": "Conferencia sobre APIs RESTful, autenticación y autorización con Laravel.",
    "cupo_maximo": 100,
    "organizador": "Organizador A"
  }
]
```

### PUT /api/eventos/{id}

Requiere token. Solo el organizador propietario del evento puede modificarlo. Cuerpo:

```json
{
  "titulo": "Conferencia Backend 2026",
  "descripcion": "Actualizada",
  "cupo_maximo": 150
}
```

Reglas de validación: `titulo` obligatorio (máximo 100 caracteres), `descripcion` opcional, `cupo_maximo` obligatorio, entero y mayor que cero.

Respuesta `200 OK`:

```json
{
  "message": "Evento actualizado con éxito.",
  "evento": {
    "id": 1,
    "titulo": "Conferencia Backend 2026",
    "descripcion": "Actualizada",
    "cupo_maximo": 150
  }
}
```

Usuario autenticado que no es el propietario, `403 Forbidden`:

```json
{ "message": "No tienes permiso para realizar esta acción." }
```

Datos inválidos, `422 Unprocessable Content`:

```json
{
  "message": "El título es obligatorio. (and 1 more error)",
  "errors": {
    "titulo": ["El título es obligatorio."],
    "cupo_maximo": ["El cupo máximo debe ser mayor que cero."]
  }
}
```

### GET /api/admin/metricas

Requiere token de un usuario con rol `admin`. Respuesta `200 OK`:

```json
{
  "total_eventos": 1,
  "ingresos_totales": 12500000,
  "moneda": "COP",
  "ingresos_simulados": true,
  "nota": "El enunciado no define un modelo de pagos. El monto de ingresos es un valor de demostración simulado, no un cálculo basado en transacciones reales."
}
```

Usuario autenticado sin rol `admin`, `403 Forbidden`:

```json
{ "message": "No tienes permiso para realizar esta acción." }
```

> **Nota sobre los ingresos:** el enunciado solicita el "monto global recaudado" pero no define ninguna tabla de pagos o ventas. Por eso `ingresos_totales` es un valor de demostración **simulado** y la respuesta lo indica con `ingresos_simulados: true`. El total de eventos sí se calcula con datos reales de la base de datos.

## Cómo probar la API

1. **Acceso público:** `GET /api/eventos` sin token debe responder `200`.
2. **Autenticación exitosa:** `POST /api/login` con una cuenta de prueba debe responder `200` con un token. Con una contraseña incorrecta debe responder `401`.
3. **Ruta protegida:** `GET /api/perfil` sin token responde `401`; con el token responde `200`.
4. **Propiedad del evento:** inicia sesión como Organizador A y como Organizador B. `PUT /api/eventos/1` con el token de A responde `200`; con el token de B responde `403` y el evento no cambia.
5. **Autorización de administrador:** `GET /api/admin/metricas` con el token del admin responde `200`; con el token de un organizador o del asistente responde `403`; sin token responde `401`.
6. **Errores de validación:** `PUT /api/eventos/1` con `{ "titulo": "", "cupo_maximo": 0 }` y el token de A responde `422`.

## Trazabilidad de requisitos

| Requisito | Implementación | Archivo |
|---|---|---|
| RF 1.1 Inicio de sesión con token Bearer | Método `login` | `app/Http/Controllers/AuthController.php` |
| RF 1.2 Consulta de perfil | Método `perfil` | `app/Http/Controllers/UserController.php` |
| RF 2.1 Consulta pública de eventos | Método `index` con relación `organizador` | `app/Http/Controllers/EventoController.php`, `app/Models/Evento.php` |
| RF 2.2 Modificación de eventos con validación | Método `update` | `app/Http/Controllers/EventoController.php` |
| RF 2.3 y RNF 2.1 Verificación de propiedad | `EventoPolicy::update` | `app/Policies/EventoPolicy.php` |
| RF 3.1 y RF 3.2 Métricas solo para administrador | Método `index` y `Gate::authorize('ver-finanzas')` | `app/Http/Controllers/MetricasController.php` |
| RNF 2.2 Gate global | `Gate::define('ver-finanzas', ...)` | `app/Providers/AppServiceProvider.php` |
| RNF 1.1 Autenticación sin estado con Sanctum | Trait `HasApiTokens` y `createToken()` | `app/Models/User.php`, `AuthController.php` |
| RNF 1.2 Contraseñas con hash | Cast `hashed` | `app/Models/User.php` |
| RNF 1.3 Protección de rutas | Middleware `auth:sanctum` | `routes/api.php` |
| RNF 3.1 Respuestas JSON y códigos HTTP | Manejo de excepciones para `/api/*` | `bootstrap/app.php` |
| Modelo de datos: tabla `users` | Migración | `database/migrations/0001_01_01_000000_create_users_table.php` |
| Modelo de datos: tabla `eventos` | Migración | `database/migrations/2026_10_13_185018_create_eventos_table.php` |
| Datos semilla | Seeder repetible | `database/seeders/DatabaseSeeder.php` |

## Decisiones de diseño

- **Autenticación sin estado:** Sanctum emite tokens personales; las rutas protegidas usan el middleware `auth:sanctum`.
- **Autorización de eventos:** `App\Policies\EventoPolicy` compara el ID del usuario autenticado con el `user_id` del evento. El controlador invoca `Gate::authorize('update', $evento)` antes de validar.
- **Autorización global:** el Gate `ver-finanzas` está definido en `App\Providers\AppServiceProvider`.
- **Asignación masiva:** `user_id` no es asignable en masa en el modelo `Evento`, por lo que el cliente no puede cambiar el propietario de un evento.
- **Contraseñas:** se almacenan con hash mediante el cast `hashed` del modelo `User`.
- **Errores en JSON:** `bootstrap/app.php` fuerza respuestas JSON en las rutas `/api/*` y define los mensajes de 401 y 403 en español.

## Publicación en GitHub

```powershell
git init
git add .
git commit -m "Version 1.0"
git branch -M main
git remote add origin https://github.com/GeorgeMakhlouf05/Taller-Laravel---Autenticacion-y-Autorizacion
git push -u origin main
```

