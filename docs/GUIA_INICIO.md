# Guía de inicio

## 1. Instalar herramientas (cada integrante, según su parte)

| Herramienta | Para qué | Quién |
|---|---|---|
| Git | Control de versiones | Todos |
| XAMPP (Apache + MySQL) | Base de datos local | Todos |
| Postman | Probar la API | Todos |
| PHP 8.2+ y Composer | Laravel | Quien trabaje en `api/` |
| Node.js 20+ y npm | Vue, Angular e Ionic | Quien trabaje en `web/` o `mobile/` |
| Ionic CLI (`npm i -g @ionic/cli`) | App móvil | Quien trabaje en `mobile/` |

## 2. Clonar el repositorio

```bash
git clone https://github.com/GermanDev22003/condia.git
cd condia
git checkout develop
```

## 3. Crear la base de datos

1. Iniciar Apache y MySQL en XAMPP.
2. Abrir phpMyAdmin (`http://localhost/phpmyadmin`).
3. Importar `docs/database/condia_schema.sql`.

## 4. Crear cada proyecto (SOLO UNA PERSONA por carpeta, una única vez)

Quien lo cree hace commit y push; los demás hacen `git pull`. No lo crea cada quien por su cuenta.

**API (`api/`)**
```bash
rm api/.gitkeep
composer create-project laravel/laravel api
cd api
# en .env: DB_CONNECTION=mysql, DB_DATABASE=condia, DB_USERNAME=root, DB_PASSWORD=
php artisan serve        # http://localhost:8000
```

**Panel web (`web/`)**
```bash
rm web/.gitkeep
npm create vue@latest web
cd web && npm install && npm run dev
```

**App móvil (`mobile/`)**
```bash
rm mobile/.gitkeep
ionic start mobile tabs --type=angular
cd mobile && ionic serve
```

## 5. Después de que otro suba cambios

```bash
git pull origin develop
# api/:      composer install  y copiar .env.example a .env
# web/ y mobile/:  npm install
```

## 6. Acordar entre equipos antes de programar

La API y el panel/app dependen entre sí. Antes de empezar, definir juntos:
- Las rutas (`/api/login`, `/api/residentes`, `/api/cuotas`, `/api/pagos`…).
- El formato del JSON de entrada y de respuesta de cada una.
Quien programe la API publica una colección de Postman en `docs/` para que el equipo de Vue e Ionic la use.
