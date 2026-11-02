# Git básico para el equipo

Se hace una vez, la primera vez:

```bash
git clone https://github.com/GermanDev22003/condia.git
cd condia
git checkout develop
```

## Cada vez que vas a trabajar en una tarea

```bash
git checkout develop                      # 1. ir a la rama develop
git pull                                  # 2. bajar lo último que subieron los demás
git checkout -b feature/PROYEC-40-nombre  # 3. crear TU rama (usa la clave de Jira)
```

## Mientras trabajas (repetir las veces que haga falta)

```bash
git status                                # ver qué archivos cambiaste
git add .                                 # preparar los cambios
git commit -m "PROYEC-40 Qué hiciste"     # guardar los cambios
```

## Cuando terminas

```bash
git push -u origin feature/PROYEC-40-nombre
```

Luego en GitHub: botón **Compare & pull request** → base `develop` → pedir revisión a un compañero.

## Si algo sale mal

| Problema | Solución |
|---|---|
| Quiero ver en qué rama estoy | `git branch` |
| Me dice que hay cambios sin guardar al cambiar de rama | `git add .` y `git commit`, o pregunta antes |
| Un compañero subió cambios | `git pull` |
| No sé qué hacer | **No uses `--force`.** Avisa al grupo |

## Reglas de oro

1. Nunca trabajar directo en `main` ni en `develop`.
2. Nunca subir `.env`, `node_modules/` ni `vendor/`.
3. Siempre `git pull` antes de empezar.
4. Un commit = una cosa.
