<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { api, ApiError } from '../api'

const textoEstado = { pendiente: 'Pendiente', en_proceso: 'En proceso', resuelta: 'Resuelta' }
const textoCategoria = {
  convivencia: 'Convivencia', limpieza: 'Limpieza', seguridad: 'Seguridad',
  ruido: 'Ruido', infraestructura: 'Infraestructura', otra: 'Otra',
}

const items = ref([])
const meta = reactive({ current_page: 1, last_page: 1, total: 0 })
const estado = ref('')
const pagina = ref(1)
const cargando = ref(false)
const error = ref('')
const aviso = ref('')

async function cargar() {
  cargando.value = true
  error.value = ''
  try {
    const r = await api('/quejas', { params: { estado: estado.value, page: pagina.value } })
    items.value = r.data
    meta.current_page = r.current_page
    meta.last_page = r.last_page
    meta.total = r.total
  } catch (e) {
    error.value = e.message
  } finally {
    cargando.value = false
  }
}

watch(estado, () => {
  pagina.value = 1
  cargar()
})
onMounted(cargar)

function irPagina(n) {
  pagina.value = n
  cargar()
}

// ---------- atender ----------
const modal = reactive({ abierto: false, queja: null })
const form = reactive({ estado: 'pendiente', respuesta: '' })
const errores = ref({})
const errorGeneral = ref('')
const guardando = ref(false)

function abrir(q) {
  modal.queja = q
  Object.assign(form, { estado: q.estado, respuesta: q.respuesta ?? '' })
  errores.value = {}
  errorGeneral.value = ''
  modal.abierto = true
}

async function guardar() {
  guardando.value = true
  errores.value = {}
  errorGeneral.value = ''
  try {
    await api(`/quejas/${modal.queja.id_queja}`, {
      method: 'PUT',
      body: { estado: form.estado, respuesta: form.respuesta.trim() || null },
    })
    modal.abierto = false
    aviso.value = 'Queja actualizada'
    await cargar()
  } catch (e) {
    if (e instanceof ApiError && e.status === 422 && Object.keys(e.errors).length) errores.value = e.errors
    else errorGeneral.value = e.message
  } finally {
    guardando.value = false
  }
}
</script>

<template>
  <div>
    <div class="barra">
      <select v-model="estado" class="filtro">
        <option value="">Todos los estados</option>
        <option value="pendiente">Pendientes</option>
        <option value="en_proceso">En proceso</option>
        <option value="resuelta">Resueltas</option>
      </select>
    </div>

    <p v-if="aviso" class="exito" @click="aviso = ''">{{ aviso }}</p>
    <p v-if="error" class="alerta">{{ error }}</p>

    <div class="caja">
      <table>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Residente</th>
            <th>Unidad</th>
            <th>Categoría</th>
            <th>Asunto</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="cargando"><td colspan="7" class="vacio">Cargando…</td></tr>
          <tr v-else-if="!items.length"><td colspan="7" class="vacio">No hay quejas que coincidan.</td></tr>
          <template v-else>
            <tr v-for="q in items" :key="q.id_queja">
              <td>{{ String(q.fecha_registro).slice(0, 10) }}</td>
              <td>{{ q.residente?.nombres }} {{ q.residente?.apellidos }}</td>
              <td>{{ q.residente?.unidad?.numero }}</td>
              <td>{{ textoCategoria[q.categoria] }}</td>
              <td>{{ q.asunto }}</td>
              <td><span class="etiqueta" :class="q.estado">{{ textoEstado[q.estado] }}</span></td>
              <td class="acciones">
                <button class="enlace" @click="abrir(q)">{{ q.estado === 'resuelta' ? 'Ver' : 'Atender' }}</button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="paginas">
      <span>Página {{ meta.current_page }} de {{ meta.last_page }} · {{ meta.total }} quejas</span>
      <span>
        <button class="btn btn-claro" :disabled="meta.current_page <= 1" @click="irPagina(meta.current_page - 1)">Anterior</button>
        <button class="btn btn-claro" :disabled="meta.current_page >= meta.last_page" @click="irPagina(meta.current_page + 1)">Siguiente</button>
      </span>
    </div>

    <div v-if="modal.abierto" class="fondo-modal" @click.self="modal.abierto = false">
      <form class="modal" @submit.prevent="guardar">
        <h2>{{ modal.queja.asunto }}</h2>
        <p class="resumen">
          {{ modal.queja.residente?.nombres }} {{ modal.queja.residente?.apellidos }} ·
          {{ modal.queja.residente?.unidad?.numero }} · {{ textoCategoria[modal.queja.categoria] }}<br />
          {{ modal.queja.descripcion }}
        </p>

        <template v-if="modal.queja.estado === 'resuelta'">
          <p><strong>Respuesta:</strong> {{ modal.queja.respuesta }}</p>
          <p class="gris">Esta queja ya está resuelta y solo se puede consultar.</p>
          <div class="botones">
            <button type="button" class="btn btn-claro" @click="modal.abierto = false">Cerrar</button>
          </div>
        </template>

        <template v-else>
          <div class="campo">
            <label>Estado</label>
            <select v-model="form.estado">
              <option value="pendiente">Pendiente</option>
              <option value="en_proceso">En proceso</option>
              <option value="resuelta">Resuelta</option>
            </select>
            <small v-if="errores.estado" class="error">{{ errores.estado[0] }}</small>
          </div>
          <div class="campo">
            <label>Respuesta al residente</label>
            <textarea v-model="form.respuesta" rows="4" maxlength="2000"></textarea>
            <small v-if="errores.respuesta" class="error">{{ errores.respuesta[0] }}</small>
          </div>
          <p v-if="errorGeneral" class="alerta">{{ errorGeneral }}</p>
          <div class="botones">
            <button type="button" class="btn btn-claro" @click="modal.abierto = false">Cancelar</button>
            <button class="btn btn-primario" :disabled="guardando">{{ guardando ? 'Guardando…' : 'Guardar' }}</button>
          </div>
        </template>
      </form>
    </div>
  </div>
</template>

<style scoped>
.barra { display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
.filtro { border: 1px solid var(--borde); border-radius: 8px; padding: 10px 12px; background: #fff; }
.exito { background: var(--verde-fondo); color: var(--verde); border-radius: 8px; padding: 10px 12px; font-size: 14px; cursor: pointer; }
.gris { color: var(--gris); }

.caja { background: #fff; border: 1px solid var(--borde); border-radius: 12px; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
th { text-align: left; font-size: 11px; letter-spacing: 0.5px; text-transform: uppercase; color: var(--gris); background: #f8fafc; padding: 12px 14px; white-space: nowrap; }
td { padding: 12px 14px; border-top: 1px solid var(--borde); }
.vacio { text-align: center; color: var(--gris); padding: 32px; }

.etiqueta { font-size: 12px; font-weight: 600; border-radius: 999px; padding: 3px 10px; white-space: nowrap; }
.etiqueta.pendiente { background: #fef9c3; color: #854d0e; }
.etiqueta.en_proceso { background: #dbeafe; color: #1e40af; }
.etiqueta.resuelta { background: var(--verde-fondo); color: var(--verde); }

.acciones { white-space: nowrap; }
.enlace { background: none; border: 0; color: var(--teal); font-weight: 600; cursor: pointer; padding: 0; }

.paginas { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; color: var(--gris); font-size: 13px; flex-wrap: wrap; gap: 8px; }
.paginas .btn { padding: 6px 12px; margin-left: 6px; }

.fondo-modal { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); display: grid; place-items: center; padding: 16px; }
.modal { background: #fff; border-radius: 14px; padding: 28px; width: 100%; max-width: 560px; max-height: 92vh; overflow-y: auto; }
.modal h2 { margin: 0 0 14px; }
.resumen { background: #f8fafc; border: 1px solid var(--borde); border-radius: 8px; padding: 10px 12px; font-size: 14px; color: var(--gris); }
.botones { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }
</style>
