# API de ConDIA (versión Sprint 3)

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
| GET | `/cuotas` | administrador | Lista paginada. Filtros: `?estado=pendiente&periodo=2026-11&id_residente=1` |
| GET | `/cuotas/{id}` | administrador | Una cuota con sus pagos y el total pagado |
| POST | `/cuotas` | administrador | Crea una cuota (estado `pendiente`) |
| POST | `/cuotas/{id}/pagos` | administrador | Registra un pago total o parcial |
| GET | `/ingresos?desde=AAAA-MM-DD&hasta=AAAA-MM-DD` | administrador | Total, cantidad y detalle de pagos del rango |
| GET | `/mis-cuotas` | residente | Sus cuotas, sus pagos y el total pendiente |
| GET | `/mis-cuotas/{id}` | residente | Una de sus cuotas con sus pagos |
| GET | `/quejas` | administrador | Lista paginada de quejas. Filtro: `?estado=pendiente` |
| PUT | `/quejas/{id}` | administrador | Cambia el estado y escribe la respuesta |
| GET | `/solicitudes-mantenimiento` | administrador | Lista paginada. Filtro: `?estado=pendiente` |
| PUT | `/solicitudes-mantenimiento/{id}` | administrador | Cambia el estado y asigna personal |
| GET | `/personal` | administrador | Personal. Filtros: `?cargo=mantenimiento&estado=activo` |
| GET | `/personal/{id}` | administrador | Un empleado |
| POST | `/personal` | administrador | Registra un empleado |
| PUT | `/personal/{id}` | administrador | Edita un empleado |
| DELETE | `/personal/{id}` | administrador | Baja lógica (estado `inactivo`) |
| GET | `/mis-quejas` | residente | Sus quejas con estado y respuesta |
| POST | `/mis-quejas` | residente | Envía una queja |
| GET | `/mis-solicitudes` | residente | Sus solicitudes con estado y personal asignado |
| POST | `/mis-solicitudes` | residente | Envía una solicitud de mantenimiento |

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

## Cuotas y pagos

**Crear una cuota** (`POST /cuotas`):
```json
{ "id_residente": 1, "concepto": "Mantenimiento", "monto": 45.00, "periodo": "2026-11", "fecha_vencimiento": "2026-11-30" }
```
Reglas: monto mayor que 0; período con formato `AAAA-MM`; el residente debe estar activo; no puede repetirse el mismo concepto y período para un residente.

**Registrar un pago** (`POST /cuotas/{id}/pagos`):
```json
{ "monto_pagado": 25.00, "fecha_pago": "2026-11-08", "metodo_pago": "efectivo", "referencia": null }
```
Reglas: `metodo_pago` es `efectivo`, `transferencia` o `cheque`; la fecha no puede ser futura; el monto no puede superar el saldo. Si el pago completa el monto, la cuota pasa a `pagada`; si no, queda `pendiente` (pago parcial).

**Estados de una cuota:** `pendiente`, `pagada` y `vencida`. Una cuota pendiente cuya fecha de vencimiento ya pasó se marca `vencida` al consultar las cuotas.

**Ingresos** (`GET /ingresos`): requiere `desde` y `hasta`; devuelve `total`, `cantidad_pagos` y `pagos`.

## Quejas y mantenimiento

**Enviar una queja** (residente, `POST /mis-quejas`):
```json
{ "asunto": "Ruido en el pasillo", "categoria": "ruido", "descripcion": "Hay ruido después de las 10 de la noche." }
```
Categorías: `convivencia`, `limpieza`, `seguridad`, `ruido`, `infraestructura`, `otra`. Nace con estado `pendiente`.

**Atender una queja** (administrador, `PUT /quejas/{id}`):
```json
{ "estado": "resuelta", "respuesta": "Se habló con los vecinos del segundo nivel." }
```
Estados: `pendiente`, `en_proceso`, `resuelta`. Para marcarla `resuelta` hace falta una respuesta, y una queja resuelta ya no se puede modificar.

**Enviar una solicitud de mantenimiento** (residente, `POST /mis-solicitudes`):
```json
{ "titulo": "Fuga en el lavamanos", "ubicacion": "Baño principal", "prioridad": "alta", "descripcion": "Gotea por debajo." }
```
Prioridades: `baja`, `media`, `alta`. Nace con estado `pendiente`.

**Atender una solicitud** (administrador, `PUT /solicitudes-mantenimiento/{id}`):
```json
{ "estado": "en_proceso", "id_personal": 1 }
```
Estados: `pendiente`, `en_proceso`, `completada`. Solo se puede asignar personal de mantenimiento activo, hace falta personal asignado para pasar a `en_proceso` o `completada`, y una solicitud completada ya no se puede modificar.

## Personal

**Registrar un empleado** (`POST /personal`):
```json
{ "nombres": "Jorge", "apellidos": "Menjívar", "cargo": "mantenimiento", "turno": "diurno", "telefono": "7100-1234" }
```
Cargos: `mantenimiento`, `seguridad`. Turnos: `diurno`, `nocturno`, `mixto`. Estados: `activo` (por defecto) e `inactivo`.

No se puede dar de baja (ni poner `inactivo`) a un empleado que tenga solicitudes de mantenimiento `en_proceso`: la API responde 422 y hay que completar o reasignar esas solicitudes primero.

## Dashboard

`GET /dashboard` (administrador). Resumen para la pantalla de inicio: `residentes_activos`, `ingresos_mes` (pagos con fecha del mes actual), `por_cobrar` (saldo de cuotas pendientes y vencidas), `cuotas_vencidas`, `quejas_abiertas`, `mantenimiento_abierto`, y las listas `quejas_recientes` y `solicitudes_recientes` (últimas 5 abiertas).

## Cambio de contraseña

`POST /cambiar-password` (cualquier usuario autenticado; máximo 10 intentos por minuto).
```json
{ "password_actual": "Residente123", "password": "NuevaClave456", "password_confirmation": "NuevaClave456" }
```
Reglas: mínimo 8 caracteres, confirmación igual y diferente de la actual. Si la contraseña actual es incorrecta responde 422 (`errors.password_actual`). Al cambiarla se cierran todas las demás sesiones del usuario; la sesión actual sigue activa.

## Códigos de error comunes

| Código | Significado |
|---|---|
| 401 | Falta el token o no es válido |
| 403 | El rol del usuario no tiene permiso para esa ruta |
| 404 | El registro no existe |
| 422 | Datos inválidos: la respuesta trae `errors` con el detalle por campo |
