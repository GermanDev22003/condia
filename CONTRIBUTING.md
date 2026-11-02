# Cómo trabajamos en el repositorio

## Ramas

- `main`: versión estable. Nadie sube directo.
- `develop`: donde se integra el trabajo del sprint.
- `feature/<CLAVE-JIRA>-<descripcion-corta>`: una rama por tarea.
  Ejemplos: `feature/PROYEC-40-login-api`, `feature/PROYEC-41-vue-residentes`.

Siempre se crea la rama desde `develop` actualizado:

```bash
git checkout develop
git pull
git checkout -b feature/PROYEC-40-login-api
```

## Commits

Mensajes cortos, en español, empezando con la clave de Jira:

```
PROYEC-40 Agrega endpoint de login con token
PROYEC-41 Crea tabla de residentes en Vue
```

Commits pequeños y frecuentes. No mezclar tareas distintas en un mismo commit.

## Pull requests

1. Subir la rama: `git push -u origin feature/PROYEC-40-login-api`.
2. Abrir un pull request hacia `develop`.
3. Otro integrante lo revisa y lo aprueba. Nadie aprueba su propio PR.
4. Se hace merge y se borra la rama.
5. Pasar la tarea a "Hecho" en Jira.

## Qué nunca se sube

- Archivos `.env` (contraseñas y llaves). Se sube solo `.env.example`.
- `vendor/` ni `node_modules/`.

## Conflictos

Antes de abrir el PR: `git pull origin develop` en tu rama y resolver conflictos ahí.
Si algo se complica, avisar al Scrum Master antes de forzar nada. Prohibido `git push --force` en `main` y `develop`.
