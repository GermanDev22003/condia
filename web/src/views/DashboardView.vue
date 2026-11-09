<script setup>
import { ref, onMounted } from 'vue'
import { api } from '../api'

const d = ref(null)
const error = ref('')

const dinero = (n) => Number(n).toLocaleString('es-SV', { style: 'currency', currency: 'USD' })
const fecha = (s) => s?.slice(0, 10)

onMounted(async () => {
  try {
    d.value = await api('/dashboard')
  } catch (e) {
    error.value = e.message
  }
})
</script>

<template>
  <div>
    <p v-if="error" class="alerta">{{ error }}</p>
    <p v-else-if="!d" class="cargando">Cargando…</p>

    <template v-else>
      <div class="tarjetas">
        <div class="tarjeta">
          <span>Residentes activos</span>
          <strong>{{ d.residentes_activos }}</strong>
        </div>
        <div class="tarjeta">
          <span>Ingresos del mes</span>
          <strong>{{ dinero(d.ingresos_mes) }}</strong>
        </div>
        <div class="tarjeta">
          <span>Por cobrar</span>
          <strong>{{ dinero(d.por_cobrar) }}</strong>
          <small>{{ d.cuotas_vencidas }} cuota(s) vencida(s)</small>
        </div>
        <RouterLink to="/quejas" class="tarjeta enlace">
          <span>Quejas abiertas</span>
          <strong>{{ d.quejas_abiertas }}</strong>
        </RouterLink>
        <RouterLink to="/mantenimiento" class="tarjeta enlace">
          <span>Mantenimiento abierto</span>
          <strong>{{ d.mantenimiento_abierto }}</strong>
        </RouterLink>
      </div>

      <div class="listas">
        <section class="caja">
          <h2>Quejas recientes</h2>
          <p v-if="!d.quejas_recientes.length" class="vacio">Sin quejas abiertas.</p>
          <ul v-else>
            <li v-for="q in d.quejas_recientes" :key="q.id_queja">
              <div>
                <b>{{ q.asunto }}</b>
                <small>{{ q.residente?.nombres }} {{ q.residente?.apellidos }} · {{ fecha(q.fecha_registro) }}</small>
              </div>
              <span class="etiqueta" :class="q.estado">{{ q.estado === 'pendiente' ? 'Pendiente' : 'En proceso' }}</span>
            </li>
          </ul>
        </section>

        <section class="caja">
          <h2>Mantenimiento reciente</h2>
          <p v-if="!d.solicitudes_recientes.length" class="vacio">Sin solicitudes abiertas.</p>
          <ul v-else>
            <li v-for="s in d.solicitudes_recientes" :key="s.id_solicitud">
              <div>
                <b>{{ s.titulo }}</b>
                <small>{{ s.residente?.nombres }} {{ s.residente?.apellidos }} · prioridad {{ s.prioridad }}</small>
              </div>
              <span class="etiqueta" :class="s.estado">{{ s.estado === 'pendiente' ? 'Pendiente' : 'En proceso' }}</span>
            </li>
          </ul>
        </section>
      </div>
    </template>
  </div>
</template>

<style scoped>
.cargando { color: var(--gris); }
.tarjetas { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
.tarjeta { background: #fff; border: 1px solid var(--borde); border-radius: 12px; padding: 18px 20px; display: flex; flex-direction: column; gap: 6px; color: inherit; text-decoration: none; }
.tarjeta span { font-size: 12px; letter-spacing: 0.5px; text-transform: uppercase; color: var(--gris); }
.tarjeta strong { font-size: 28px; color: var(--navy); }
.tarjeta small { color: var(--rojo); font-size: 12px; }
.tarjeta.enlace:hover { border-color: var(--teal); }
.listas { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.caja { background: #fff; border: 1px solid var(--borde); border-radius: 12px; padding: 18px 20px; }
.caja h2 { margin: 0 0 12px; font-size: 16px; }
ul { list-style: none; margin: 0; padding: 0; }
li { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 10px 0; border-top: 1px solid var(--borde); }
li div { display: flex; flex-direction: column; }
li small { color: var(--gris); font-size: 12px; }
.vacio { color: var(--gris); }
.etiqueta { font-size: 12px; font-weight: 600; border-radius: 999px; padding: 3px 10px; white-space: nowrap; background: var(--rojo-fondo); color: var(--rojo); }
.etiqueta.en_proceso { background: #fef3c7; color: #b45309; }
@media (max-width: 800px) { .listas { grid-template-columns: 1fr; } }
</style>
