# ConDIA · Panel web (Vue 3 + Vite)

Panel administrativo del condominio. Se conecta a la API de `api/`.

## Cómo correrlo

1. Tener la API corriendo: en `api/`, `php artisan serve` (queda en `http://127.0.0.1:8000`).
2. En esta carpeta (`web/`):
   ```bash
   npm install
   npm run dev
   ```
3. Abrir `http://localhost:5173` e iniciar sesión con `admin@condia.test` / `Admin12345`.

Si la API está en otra dirección, copia `.env.example` a `.env` y cambia `VITE_API_URL`.

## Estructura

| Archivo | Para qué sirve |
|---|---|
| `src/api.js` | Función `api()` que llama a la API con el token y maneja errores |
| `src/session.js` | Guarda el token y el usuario (en memoria y en `localStorage`) |
| `src/auth.js` | `login()` y `logout()` |
| `src/router.js` | Rutas y protección (sin sesión → login) |
| `src/layouts/PanelLayout.vue` | Menú lateral y encabezado |
| `src/views/LoginView.vue` | Pantalla de inicio de sesión |
| `src/views/ResidentesView.vue` | Tabla, búsqueda y formulario de residentes |
| `src/views/ProximamenteView.vue` | Marcador de los módulos que faltan |
