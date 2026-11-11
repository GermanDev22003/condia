<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { api, ApiError } from '../api'

const textoEstado = { pendiente: 'Pendiente', en_proceso: 'En proceso', completada: 'Completada' }
const textoPrioridad = { baja: 'Baja', media: 'Media', alta: 'Alta' }

const items = ref([])
const meta = reactive({ current_page: 1, last_page: 1, total: 0 })
const estado = ref('')
const pagina = ref(1)
const cargando = ref(false)
const error = ref('')
const aviso = ref('')
const personal = ref([])

async function cargar() {
  cargando.value = true
  error.value = ''
  try {
    const r = await api('/solicitudes-mantenimiento', { params: { estado: estado.value, page: pagina.value } })
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

onMounted(async () => {
  cargar()
  try {
    personal.value = await api('/personal', { params: { cargo: 'mantenimiento', estado: 'activo' } })
  } catch (e) {
    error.value = e.message
  }
})

function irPagina(n) {
  pagina.value = n
  cargar()
}

// ---------- atender ----------
const modal = reactive({ abierto: false, solicitud: null })
const form = reactive({ estado: 'pendiente', id_personal: '' })
const errores = ref({})
const errorGeneral = ref('')
const guardando = ref(false)

function abrir(s) {
  modal.solicitud = s
  Object.assign(form, { estado: s.estado, id_personal: s.id_personal ?? '' })
  errores.value = {}
  errorGeneral.value = ''
  modal.abierto = true
}

async function guardar() {
  guardando.value = true
  errores.value = {}
  errorGeneral.value = ''
  try {
    await api(`/solicitudes-mantenimiento/${modal.solicitud.id_solicitud}`, {
      method: 'PUT',
      body: { estado: form.estado, id_personal: form.id_personal ? Number(form.id_personal) : null },
    })
    modal.abierto = false
    aviso.value = 'Solicitud actualizada'
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
        <option value="completada">Completadas</option>
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
            <th>Solicitud</th>
            <th>Prioridad</th>
            <th>Asignado a</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="cargando"><td colspan="8" class="vacio">Cargando…</td></tr>
          <tr v-else-if="!items.length"><td colspan="8" class="vacio">No hay solicitudes que coincidan.</td></tr>
          <template v-else>
            <tr v-for="s in items" :key="s.id_solicitud">
              <td>{{ String(s.fecha_registro).slice(0, 10) }}</td>
              <td>{{ s.residente?.nombres }} {{ s.residente?.apellidos }}</td>
              <td>{{ s.residente?.unidad?.numero }}</td>
              <td>{{ s.titulo }}<br /><span class="gris">{{ s.ubicacion }}</span></td>
              <td><span class="prio" :class="s.prioridad">{{ textoPrioridad[s.prioridad] }}</span></td>
              <td>{{ s.personal ? s.personal.nombres + ' ' + s.personal.apellidos : 'Sin asignar' }}</td>
              <td><span class="etiqueta" :class="s.estado">{{ textoEstado[s.estado] }}</span></td>
              <td class="acciones">
                <button class="enlace" @click="abrir(s)">{{ s.estado === 'completada' ? 'Ver' : 'Atender' }}</button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="paginas">
      <span>Página {{ meta.current_page }} de {{ meta.last_page }} · {{ meta.total }} solicitudes</span>
      <span>
        <button class="btn btn-claro" :disabled="meta.current_page <= 1" @click="irPagina(meta.current_page - 1)">Anterior</button>
        <button class="btn btn-claro" :disabled="meta.current_page >= meta.last_page" @click="irPagina(meta.current_page + 1)">Siguiente</button>
      </span>
    </div>

    <div v-if="modal.abierto" class="fondo-modal" @click.self="modal.abierto = false">
      <form class="modal" @submit.prevent="guardar">
        <h2>{{ modal.solicitud.titulo }}</h2>
        <p class="resumen">
          {{ modal.solicitud.residente?.nombres }} {{ modal.solicitud.residente?.apellidos }} ·
          {{ modal.solicitud.residente?.unidad?.numero }} · {{ modal.solicitud.ubicacion }} ·
          Prioridad {{ textoPrioridad[modal.solicitud.prioridad] }}<br />
          {{ modal.solicitud.descripcion }}
        </p>

        <template v-if="modal.solicitud.estado === 'completada'">
          <p class="gris">Esta solicitud ya está completada y solo se puede consultar.</p>
          <div class="botones">
            <button type="button" class="btn btn-claro" @click="modal.abierto = false">Cerrar</button>
          </div>
        </template>

        <template v-else>
          <div class="fila">
            <div class="campo">
              <label>Asignar a</label>
              <select v-model="form.id_personal">
                <option value="">Sin asignar</option>
                <option v-for="p in personal" :key="p.id_personal" :value="p.id_personal">
                  {{ p.nombres }} {{ p.apellidos }} · {{ p.turno }}
                </option>
              </select>
              <small v-if="errores.id_personal" class="error">{{ errores.id_personal[0] }}</small>
            </div>
            <div class="campo">
              <label>Estado</label>
              <select v-model="form.estado">
                <option value="pendiente">Pendiente</option>
                <option value="en_proceso">En proceso</option>
                <option value="completada">Completada</option>
              </select>
              <small v-if="errores.estado" class="error">{{ errores.estado[0] }}</small>
            </div>
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
.gris { color: var(--gris); font-size: 13px; }

.caja { background: #fff; border: 1px solid var(--borde); border-radius: 12px; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
th { text-align: left; font-size: 11px; letter-spacing: 0.5px; text-transform: uppercase; color: var(--gris); background: #f8fafc; padding: 12px 14px; white-space: nowrap; }
td { padding: 12px 14px; border-top: 1px solid var(--borde); }
.vacio { text-align: center; color: var(--gris); padding: 32px; }

.etiqueta, .prio { font-size: 12px; font-weight: 600; border-radius: 999px; padding: 3px 10px; white-space: nowrap; }
.etiqueta.pendiente { background: #fef9c3; color: #854d0e; }
.etiqueta.en_proceso { background: #dbeafe; color: #1e40af; }
.etiqueta.completada { background: var(--verde-fondo); color: var(--verde); }
.prio.baja { background: #f1f5f9; color: var(--gris); }
.prio.media { background: #fef9c3; color: #854d0e; }
.prio.alta { background: var(--rojo-fondo); color: var(--rojo); }

.acciones { white-space: nowrap; }
.enlace { background: none; border: 0; color: var(--teal); font-weight: 600; cursor: pointer; padding: 0; }

.paginas { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; color: var(--gris); font-size: 13px; flex-wrap: wrap; gap: 8px; }
.paginas .btn { padding: 6px 12px; margin-left: 6px; }

.fondo-modal { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); display: grid; place-items: center; padding: 16px; }
.modal { background: #fff; border-radius: 14px; padding: 28px; width: 100%; max-width: 560px; max-height: 92vh; overflow-y: auto; }
.modal h2 { margin: 0 0 14px; }
.resumen { background: #f8fafc; border: 1px solid var(--borde); border-radius: 8px; padding: 10px 12px; font-size: 14px; color: var(--gris); }
.fila { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.botones { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }
@media (max-width: 560px) { .fila { grid-template-columns: 1fr; } }
</style>
