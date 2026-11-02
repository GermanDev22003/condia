# ConDIA · Sistema de gestión de condominios

Proyecto final · ITCA-FEPADE · Técnico en Ingeniería de Software · Ciclo 02-2026
Materias: Desarrollo de Aplicaciones Móviles, Utilización de Frameworks y Metodologías Ágiles.

ConDIA centraliza la administración de un condominio (edificio o residencial): residentes, cuotas, pagos, quejas, solicitudes de mantenimiento, personal e ingresos registrados.

## Componentes

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
| Emmy Esthefani Rosa Castellanos | Scrum team |
| Nestor Steve Martinez Serrano | Developer|
| Guillermo Isaías Rivera Antillon | Developer |
