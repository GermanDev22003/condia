<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { api, ApiError } from '../api'

// ---------- lista ----------
const items = ref([])
const meta = reactive({ current_page: 1, last_page: 1, total: 0 })
const buscar = ref('')
const estado = ref('')
const pagina = ref(1)
const cargando = ref(false)
const error = ref('')
const unidades = ref([])

async function cargar() {
  cargando.value = true
  error.value = ''
  try {
    const r = await api('/residentes', {
      params: { buscar: buscar.value, estado: estado.value, page: pagina.value },
    })
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

let temporizador
watch(buscar, () => {
  clearTimeout(temporizador)
  temporizador = setTimeout(() => {
    pagina.value = 1
    cargar()
  }, 300)
})
watch(estado, () => {
  pagina.value = 1
  cargar()
})

function irPagina(n) {
  pagina.value = n
  cargar()
}

onMounted(async () => {
  cargar()
  try {
    unidades.value = await api('/unidades')
  } catch (e) {
    error.value = e.message
  }
})

// ---------- formulario (crear / editar) ----------
const hoy = () => new Date().toISOString().slice(0, 10)
const formVacio = () => ({
  id_unidad: '',
  nombres: '',
  apellidos: '',
  telefono: '',
  fecha_ingreso: hoy(),
  estado: 'activo',
  crearAcceso: false,
  correo: '',
  password: '',
})

const modal = reactive({ abierto: false, id: null, tieneUsuario: false })
const form = reactive(formVacio())
const errores = ref({})
const errorGeneral = ref('')
const guardando = ref(false)

function abrirCrear() {
  Object.assign(form, formVacio())
  modal.id = null
  modal.tieneUsuario = false
  errores.value = {}
  errorGeneral.value = ''
  modal.abierto = true
}

function abrirEditar(r) {
  Object.assign(form, {
    id_unidad: r.id_unidad,
    nombres: r.nombres,
    apellidos: r.apellidos,
    telefono: r.telefono ?? '',
    fecha_ingreso: r.fecha_ingreso,
    estado: r.estado,
    crearAcceso: false,
    correo: r.usuario?.correo ?? '',
    password: '',
  })
  modal.id = r.id_residente
  modal.tieneUsuario = !!r.usuario
  errores.value = {}
  errorGeneral.value = ''
  modal.abierto = true
}

async function guardar() {
  guardando.value = true
  errores.value = {}
  errorGeneral.value = ''

  const base = {
    id_unidad: Number(form.id_unidad),
    nombres: form.nombres.trim(),
    apellidos: form.apellidos.trim(),
    telefono: form.telefono.trim() || null,
    fecha_ingreso: form.fecha_ingreso,
  }

  try {
    if (modal.id) {
      const body = { ...base, estado: form.estado }
      if (modal.tieneUsuario) body.correo = form.correo.trim()
      await api(`/residentes/${modal.id}`, { method: 'PUT', body })
    } else {
      const body = { ...base }
      if (form.crearAcceso) {
        body.correo = form.correo.trim()
        body.password = form.password
      }
      await api('/residentes', { method: 'POST', body })
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

async function darDeBaja(r) {
  if (!confirm(`¿Dar de baja a ${r.nombres} ${r.apellidos}? Su usuario dejará de poder entrar.`)) return
  try {
    await api(`/residentes/${r.id_residente}`, { method: 'DELETE' })
    await cargar()
  } catch (e) {
    error.value = e.message
  }
}
</script>

<template>
  <div>
    <div class="barra">
      <input v-model="buscar" class="buscador" type="search" placeholder="Buscar por nombre o unidad..." />
      <select v-model="estado" class="filtro">
        <option value="">Todos</option>
        <option value="activo">Activos</option>
        <option value="inactivo">Inactivos</option>
      </select>
      <button class="btn btn-primario" @click="abrirCrear">+ Nuevo residente</button>
    </div>

    <p v-if="error" class="alerta">{{ error }}</p>

    <div class="caja">
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Unidad</th>
            <th>Teléfono</th>
            <th>Correo de acceso</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="cargando">
            <td colspan="6" class="vacio">Cargando…</td>
          </tr>
          <tr v-else-if="!items.length">
            <td colspan="6" class="vacio">No hay residentes que coincidan.</td>
          </tr>
          <template v-else>
            <tr v-for="r in items" :key="r.id_residente">
              <td>{{ r.nombres }} {{ r.apellidos }}</td>
              <td>{{ r.unidad?.numero }}</td>
              <td>{{ r.telefono || '—' }}</td>
              <td>{{ r.usuario?.correo || 'Sin acceso' }}</td>
              <td>
                <span class="etiqueta" :class="r.estado">{{ r.estado === 'activo' ? 'Activo' : 'Inactivo' }}</span>
              </td>
              <td class="acciones">
                <button class="enlace" @click="abrirEditar(r)">Editar</button>
                <template v-if="r.estado === 'activo'">
                  · <button class="enlace peligro" @click="darDeBaja(r)">Dar de baja</button>
                </template>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="paginas">
      <span>Página {{ meta.current_page }} de {{ meta.last_page }} · {{ meta.total }} residentes</span>
      <span>
        <button class="btn btn-claro" :disabled="meta.current_page <= 1" @click="irPagina(meta.current_page - 1)">Anterior</button>
        <button class="btn btn-claro" :disabled="meta.current_page >= meta.last_page" @click="irPagina(meta.current_page + 1)">Siguiente</button>
      </span>
    </div>

    <!-- Ventana del formulario -->
    <div v-if="modal.abierto" class="fondo-modal" @click.self="modal.abierto = false">
      <form class="modal" @submit.prevent="guardar">
        <h2>{{ modal.id ? 'Editar residente' : 'Nuevo residente' }}</h2>

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
            <label>Unidad</label>
            <select v-model="form.id_unidad" required>
              <option value="" disabled>Selecciona…</option>
              <option v-for="u in unidades" :key="u.id_unidad" :value="u.id_unidad">
                {{ u.numero }} · {{ u.torre_bloque }}
              </option>
            </select>
            <small v-if="errores.id_unidad" class="error">{{ errores.id_unidad[0] }}</small>
          </div>
          <div class="campo">
            <label>Teléfono</label>
            <input v-model="form.telefono" maxlength="20" />
            <small v-if="errores.telefono" class="error">{{ errores.telefono[0] }}</small>
          </div>
        </div>

        <div class="fila">
          <div class="campo">
            <label>Fecha de ingreso</label>
            <input v-model="form.fecha_ingreso" type="date" required />
            <small v-if="errores.fecha_ingreso" class="error">{{ errores.fecha_ingreso[0] }}</small>
          </div>
          <div v-if="modal.id" class="campo">
            <label>Estado</label>
            <select v-model="form.estado">
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
          </div>
        </div>

        <!-- Usuario de acceso -->
        <template v-if="!modal.id">
          <label class="casilla">
            <input v-model="form.crearAcceso" type="checkbox" /> Crear usuario de acceso a la app
          </label>
          <div v-if="form.crearAcceso" class="fila">
            <div class="campo">
              <label>Correo</label>
              <input v-model="form.correo" type="email" required />
              <small v-if="errores.correo" class="error">{{ errores.correo[0] }}</small>
            </div>
            <div class="campo">
              <label>Contraseña (mínimo 8)</label>
              <input v-model="form.password" type="password" minlength="8" required />
              <small v-if="errores.password" class="error">{{ errores.password[0] }}</small>
            </div>
          </div>
        </template>
        <div v-else-if="modal.tieneUsuario" class="campo">
          <label>Correo de acceso</label>
          <input v-model="form.correo" type="email" required />
          <small v-if="errores.correo" class="error">{{ errores.correo[0] }}</small>
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
.buscador { flex: 1; min-width: 220px; max-width: 360px; border: 1px solid var(--borde); border-radius: 8px; padding: 10px 12px; background: #fff; }
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

.paginas { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; color: var(--gris); font-size: 13px; flex-wrap: wrap; gap: 8px; }
.paginas .btn { padding: 6px 12px; margin-left: 6px; }

.fondo-modal { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); display: grid; place-items: center; padding: 16px; }
.modal { background: #fff; border-radius: 14px; padding: 28px; width: 100%; max-width: 560px; max-height: 92vh; overflow-y: auto; }
.modal h2 { margin: 0 0 18px; }
.fila { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.casilla { display: flex; gap: 8px; align-items: center; margin-bottom: 14px; font-size: 14px; }
.botones { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }
@media (max-width: 560px) { .fila { grid-template-columns: 1fr; } }
</style>
