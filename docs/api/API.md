# API de ConDIA (versión Sprint 2)

Base: `http://127.0.0.1:8000/api` · Formato: JSON · Autenticación: `Authorization: Bearer <token>`

Colección lista para importar en Postman: [`ConDIA.postman_collection.json`](ConDIA.postman_collection.json) (Import → arrastrar el archivo; ejecuta primero "Login administrador" y el token se guarda solo).

En Postman, para peticiones con cuerpo: pestaña **Body → raw → JSON**, y añade el encabezado `Accept: application/json`.

## Usuarios de prueba (los crea `php artisan migrate --seed`)

| Rol | Correo | Contraseña |
|---|---|---|
| administrador | `admin@condia.test` | `Admin12345` |
| residente | `maria@condia.test` | `Residente123` |

## Rutas

| Método | Ruta | Quién | Qué hace |
|---|---|---|---|
| POST | `/login` | público | Devuelve el token |
| POST | `/logout` | con token | Invalida el token actual |
| GET | `/me` | con token | Datos del usuario que inició sesión |
| GET | `/unidades` | administrador | Lista de unidades (para los formularios) |
| GET | `/residentes` | administrador | Lista paginada. Filtros: `?buscar=texto&estado=activo&page=2` |
| GET | `/residentes/{id}` | administrador | Un residente |
| POST | `/residentes` | administrador | Crea un residente (y opcionalmente su usuario) |
| PUT | `/residentes/{id}` | administrador | Edita un residente |
| DELETE | `/residentes/{id}` | administrador | Baja lógica (estado `inactivo`) |

## Ejemplos

**POST /login**
```json
{ "correo": "admin@condia.test", "password": "Admin12345" }
```
Respuesta 200:
```json
{
  "token": "…64 caracteres…",
  "tipo": "Bearer",
  "usuario": { "id_usuario": 1, "correo": "admin@condia.test", "rol": "administrador", "residente": null }
}
```
Errores: `401` credenciales incorrectas · `422` datos inválidos · `429` demasiados intentos.

**POST /residentes** (con usuario de acceso; `correo` y `password` son opcionales)
```json
{
  "id_unidad": 5,
  "nombres": "Laura",
  "apellidos": "Gómez Rivas",
  "telefono": "7000-5555",
  "fecha_ingreso": "2026-11-10",
  "correo": "laura@condia.test",
  "password": "Residente123"
}
```
Respuesta 201: el residente con su `unidad` y `usuario`.

**PUT /residentes/{id}** — solo los campos que cambian:
```json
{ "telefono": "7000-9999", "id_unidad": 3 }
```

**Dar acceso a un residente que no tiene usuario** (`correo` y `password` juntos, contraseña mínimo 8):
```json
{ "correo": "roberto@condia.test", "password": "Residente123" }
```

**Restablecer la contraseña** de un residente que ya tiene usuario (cierra sus sesiones abiertas):
```json
{ "password": "NuevaClave123" }
```

## Códigos de error comunes

| Código | Significado |
|---|---|
| 401 | Falta el token o no es válido |
| 403 | El usuario no es administrador |
| 404 | El registro no existe |
| 422 | Datos inválidos: la respuesta trae `errors` con el detalle por campo |
