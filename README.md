# ConDIA · Sistema de gestión de condominios

Proyecto final · ITCA-FEPADE · Técnico en Ingeniería de Software · Ciclo 02-2026
Materias: Desarrollo de Aplicaciones Móviles, Utilización de Frameworks y Metodologías Ágiles.

ConDIA centraliza la administración de un condominio (edificio o residencial): residentes, cuotas, pagos, quejas, solicitudes de mantenimiento, personal e ingresos registrados.

## Componentes

| Carpeta | Qué es | Tecnología |
|---|---|---|
| `api/` | API REST que aplica las reglas del negocio y habla con la base de datos | PHP · Laravel |
| `web/` | Panel administrativo | Vue |
| `mobile/` | App del residente | Angular · Ionic |
| `docs/` | Documentación, modelo de datos, mockups y script SQL | — |

```
Vue (web) ─┐
           ├─ HTTP + JSON ─→ Laravel (api) ─→ MySQL (XAMPP)
Ionic app ─┘
```

## Equipo

| Integrante | Rol |
|---|---|
| German Josué Mena Solórzano | Product Owner |
| Jose Javier Callejas Rivas | Scrum Master |
| Emmy Esthefani Rosa Castellanos | Panel web (Vue) |
| Nestor Steve Martinez Serrano | API (Laravel) y app móvil (Ionic) |
| Guillermo Isaías Rivera Antillon | Panel web (Vue) |

## Cómo empezar

Guía de instalación y de creación de cada proyecto: [`docs/GUIA_INICIO.md`](docs/GUIA_INICIO.md).
Reglas de trabajo en equipo (ramas, commits, pull requests): [`CONTRIBUTING.md`](CONTRIBUTING.md).

## Documentación

- Documento del Sprint 1: [`docs/ConDIA_Sprint1_Entregables.docx`](docs/ConDIA_Sprint1_Entregables.docx)
- Script de la base de datos: [`docs/database/condia_schema.sql`](docs/database/condia_schema.sql)
- Diagramas: [`docs/diagramas/`](docs/diagramas/)
- Mockups: [`docs/mockups/`](docs/mockups/)

## Alcance de la versión 1

Un solo condominio, sin gestión de gastos y sin integración con bancos ni pasarelas de pago.
