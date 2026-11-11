<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { api, ApiError } from '../api'

const items = ref([])
const cargo = ref('')
const estado = ref('')
const cargando = ref(false)
const error = ref('')

async function cargar() {
  cargando.value = true
  error.value = ''
  try {
    items.value = await api('/personal', { params: { cargo: cargo.value, estado: estado.value } })
  } catch (e) {
    error.value = e.message
  } finally {
    cargando.value = false
  }
}
watch([cargo, estado], cargar)
onMounted(cargar)

const formVacio = () => ({ nombres: '', apellidos: '', cargo: 'mantenimiento', turno: 'diurno', telefono: '', estado: 'activo' })
const modal = reactive({ abierto: false, id: null })
const form = reactive(formVacio())
const errores = ref({})
const errorGeneral = ref('')
const guardando = ref(false)

function abrirCrear() {
  Object.assign(form, formVacio())
  modal.id = null
  errores.value = {}
  errorGeneral.value = ''
  modal.abierto = true
}

function abrirEditar(p) {
  Object.assign(form, {
    nombres: p.nombres,
    apellidos: p.apellidos,
    cargo: p.cargo,
    turno: p.turno,
    telefono: p.telefono ?? '',
    estado: p.estado,
  })
  modal.id = p.id_personal
  errores.value = {}
  errorGeneral.value = ''
  modal.abierto = true
}

async function guardar() {
  guardando.value = true
  errores.value = {}
  errorGeneral.value = ''
  const body = {
    nombres: form.nombres.trim(),
    apellidos: form.apellidos.trim(),
    cargo: form.cargo,
    turno: form.turno,
    telefono: form.telefono.trim() || null,
  }
  try {
    if (modal.id) {
      await api(`/personal/${modal.id}`, { method: 'PUT', body: { ...body, estado: form.estado } })
    } else {
      await api('/personal', { method: 'POST', body })
    }
    modal.abierto = false
    await cargar()
  } catch (e) {
    if (e instanceof ApiError && e.status === 422 && Object.keys(e.errors).length) {
      errores.value = e.errors
    } else {
      errorGeneral.value = e.message
    }
  } finally {
    guardando.value = false
  }
}

async function darDeBaja(p) {
  if (!confirm(`¿Dar de baja a ${p.nombres} ${p.apellidos}?`)) return
  error.value = ''
  try {
    await api(`/personal/${p.id_personal}`, { method: 'DELETE' })
    await cargar()
  } catch (e) {
    error.value = e instanceof ApiError && e.errors?.estado ? e.errors.estado[0] : e.message
  }
}

const cargos = { mantenimiento: 'Mantenimiento', seguridad: 'Seguridad' }
const turnos = { diurno: 'Diurno', nocturno: 'Nocturno', mixto: 'Mixto' }
</script>

<template>
  <div>
    <div class="barra">
      <select v-model="cargo" class="filtro">
        <option value="">Todos los cargos</option>
        <option value="mantenimiento">Mantenimiento</option>
        <option value="seguridad">Seguridad</option>
      </select>
      <select v-model="estado" class="filtro">
        <option value="">Todos</option>
        <option value="activo">Activos</option>
        <option value="inactivo">Inactivos</option>
      </select>
      <button class="btn btn-primario" @click="abrirCrear">+ Nuevo empleado</button>
    </div>

    <p v-if="error" class="alerta">{{ error }}</p>

    <div class="caja">
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Cargo</th>
            <th>Turno</th>
            <th>Teléfono</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="cargando"><td colspan="6" class="vacio">Cargando…</td></tr>
          <tr v-else-if="!items.length"><td colspan="6" class="vacio">No hay personal que coincida.</td></tr>
          <template v-else>
            <tr v-for="p in items" :key="p.id_personal">
              <td>{{ p.nombres }} {{ p.apellidos }}</td>
              <td>{{ cargos[p.cargo] }}</td>
              <td>{{ turnos[p.turno] }}</td>
              <td>{{ p.telefono || '—' }}</td>
              <td><span class="etiqueta" :class="p.estado">{{ p.estado === 'activo' ? 'Activo' : 'Inactivo' }}</span></td>
              <td class="acciones">
                <button class="enlace" @click="abrirEditar(p)">Editar</button>
                <template v-if="p.estado === 'activo'">
                  · <button class="enlace peligro" @click="darDeBaja(p)">Dar de baja</button>
                </template>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div v-if="modal.abierto" class="fondo-modal" @click.self="modal.abierto = false">
      <form class="modal" @submit.prevent="guardar">
        <h2>{{ modal.id ? 'Editar empleado' : 'Nuevo empleado' }}</h2>

        <div class="fila">
          <div class="campo">
            <label>Nombres</label>
            <input v-model="form.nombres" required maxlength="100" />
            <small v-if="errores.nombres" class="error">{{ errores.nombres[0] }}</small>
          </div>
          <div class="campo">
            <label>Apellidos</label>
            <input v-model="form.apellidos" required maxlength="100" />
            <small v-if="errores.apellidos" class="error">{{ errores.apellidos[0] }}</small>
          </div>
        </div>

        <div class="fila">
          <div class="campo">
            <label>Cargo</label>
            <select v-model="form.cargo">
              <option value="mantenimiento">Mantenimiento</option>
              <option value="seguridad">Seguridad</option>
            </select>
            <small v-if="errores.cargo" class="error">{{ errores.cargo[0] }}</small>
          </div>
          <div class="campo">
            <label>Turno</label>
            <select v-model="form.turno">
              <option value="diurno">Diurno</option>
              <option value="nocturno">Nocturno</option>
              <option value="mixto">Mixto</option>
            </select>
            <small v-if="errores.turno" class="error">{{ errores.turno[0] }}</small>
          </div>
        </div>

        <div class="fila">
          <div class="campo">
            <label>Teléfono</label>
            <input v-model="form.telefono" maxlength="20" />
            <small v-if="errores.telefono" class="error">{{ errores.telefono[0] }}</small>
          </div>
          <div v-if="modal.id" class="campo">
            <label>Estado</label>
            <select v-model="form.estado">
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
            <small v-if="errores.estado" class="error">{{ errores.estado[0] }}</small>
          </div>
        </div>

        <p v-if="errorGeneral" class="alerta">{{ errorGeneral }}</p>

        <div class="botones">
          <button type="button" class="btn btn-claro" @click="modal.abierto = false">Cancelar</button>
          <button class="btn btn-primario" :disabled="guardando">{{ guardando ? 'Guardando…' : 'Guardar' }}</button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.barra { display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
.filtro { border: 1px solid var(--borde); border-radius: 8px; padding: 10px 12px; background: #fff; }
.barra .btn-primario { margin-left: auto; }
.caja { background: #fff; border: 1px solid var(--borde); border-radius: 12px; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
th { text-align: left; font-size: 11px; letter-spacing: 0.5px; text-transform: uppercase; color: var(--gris); background: #f8fafc; padding: 12px 16px; }
td { padding: 13px 16px; border-top: 1px solid var(--borde); }
.vacio { text-align: center; color: var(--gris); padding: 32px; }
.etiqueta { font-size: 12px; font-weight: 600; border-radius: 999px; padding: 3px 10px; }
.etiqueta.activo { background: var(--verde-fondo); color: var(--verde); }
.etiqueta.inactivo { background: var(--rojo-fondo); color: var(--rojo); }
.acciones { white-space: nowrap; }
.enlace { background: none; border: 0; color: var(--teal); font-weight: 600; cursor: pointer; padding: 0; }
.enlace.peligro { color: var(--rojo); }
.fondo-modal { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); display: grid; place-items: center; padding: 16px; }
.modal { background: #fff; border-radius: 14px; padding: 28px; width: 100%; max-width: 520px; max-height: 92vh; overflow-y: auto; }
.modal h2 { margin: 0 0 18px; }
.fila { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.botones { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }
@media (max-width: 560px) { .fila { grid-template-columns: 1fr; } }
</style>
