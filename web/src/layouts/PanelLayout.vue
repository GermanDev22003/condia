<script setup>
import { useRoute, useRouter } from 'vue-router'
import { session } from '../session'
import { logout } from '../auth'

const route = useRoute()
const router = useRouter()

const menu = [
  { ruta: '/dashboard', texto: 'Dashboard' },
  { ruta: '/residentes', texto: 'Residentes' },
  { ruta: '/cuotas', texto: 'Cuotas y pagos' },
  { ruta: '/quejas', texto: 'Quejas' },
  { ruta: '/mantenimiento', texto: 'Mantenimiento' },
  { ruta: '/personal', texto: 'Personal' },
  { ruta: '/ingresos', texto: 'Ingresos' },
]

async function salir() {
  await logout()
  router.push('/login')
}
</script>

<template>
  <div class="panel">
    <aside class="lateral">
      <div class="logo">Con<span>DIA</span></div>
      <nav>
        <RouterLink v-for="m in menu" :key="m.ruta" :to="m.ruta">{{ m.texto }}</RouterLink>
      </nav>
      <button class="salir" @click="salir">Cerrar sesión</button>
    </aside>

    <div class="contenido">
      <header class="encabezado">
        <h1>{{ route.meta.titulo }}</h1>
        <span class="usuario">Administrador · {{ session.usuario?.correo }}</span>
      </header>
      <main class="cuerpo">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<style scoped>
.panel { display: flex; min-height: 100vh; }

.lateral {
  width: 230px;
  flex-shrink: 0;
  background: var(--navy);
  color: #cbd5e1;
  display: flex;
  flex-direction: column;
  padding: 22px 14px;
}
.lateral .logo { color: #fff; padding: 0 10px 22px; }
.lateral .logo span { color: var(--teal-claro); }

nav { display: flex; flex-direction: column; gap: 4px; flex: 1; }
nav a {
  color: inherit;
  text-decoration: none;
  padding: 10px 12px;
  border-radius: 8px;
}
nav a:hover { background: rgba(255, 255, 255, 0.06); }
nav a.router-link-active { background: var(--teal); color: #fff; font-weight: 600; }

.salir {
  background: none;
  border: 0;
  color: #cbd5e1;
  text-align: left;
  padding: 10px 12px;
  cursor: pointer;
}
.salir:hover { color: #fff; }

.contenido { flex: 1; min-width: 0; }
.encabezado {
  background: #fff;
  border-bottom: 1px solid var(--borde);
  padding: 16px 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}
.encabezado h1 { margin: 0; font-size: 18px; }
.usuario { color: var(--gris); font-size: 13px; }
.cuerpo { padding: 24px 28px; }

@media (max-width: 800px) {
  .panel { flex-direction: column; }
  .lateral { width: 100%; flex-direction: row; align-items: center; overflow-x: auto; gap: 8px; padding: 10px; }
  .lateral .logo { padding: 0 8px; }
  nav { flex-direction: row; }
  nav a, .salir { white-space: nowrap; }
}
</style>
