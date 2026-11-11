# ConDIA · App del residente (Ionic + Angular)

Se conecta a la API de `api/`. Solo entran usuarios con rol **residente**.

## Cómo correrla

1. API corriendo: en `api/`, `php artisan serve`.
2. En esta carpeta (`mobile/`):
   ```bash
   npm install
   ionic serve
   ```
3. Se abre en el navegador. Para verla como celular: `F12` → modo dispositivo.
4. Entrar con `maria@condia.test` / `Residente123`.

La dirección de la API está en `src/environments/environment.ts` (`apiUrl`).

## Estructura (`src/app/`)

| Carpeta | Contenido |
|---|---|
| `core/` | Sesión (token), llamadas a la API, login/logout y guards de rutas |
| `login/` | Pantalla de inicio de sesión (mockup `mov_01`) |
| `inicio/` | Pantalla de inicio del residente (mockup `mov_02`) |
