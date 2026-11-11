<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { api, ApiError } from '../api'

const dinero = (n) => '$' + Number(n || 0).toFixed(2)
const hoy = () => new Date().toISOString().slice(0, 10)
const mesActual = () => new Date().toISOString().slice(0, 7)
const saldo = (c) => Number(c.monto) - Number(c.total_pagado || 0)
const textoEstado = { pendiente: 'Pendiente', pagada: 'Pagada', vencida: 'Vencida' }

// ---------- lista ----------
const items = ref([])
const meta = reactive({ current_page: 1, last_page: 1, total: 0 })
const estado = ref('')
const periodo = ref('')
const pagina = ref(1)
const cargando = ref(false)
const error = ref('')
const aviso = ref('')
const residentes = ref([])

async function cargar() {
  cargando.value = true
  error.value = ''
  try {
    const r = await api('/cuotas', { params: { estado: estado.value, periodo: periodo.value, page: pagina.value } })
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

watch([estado, periodo], () => {
  pagina.value = 1
  cargar()
})

function irPagina(n) {
  pagina.value = n
  cargar()
}

async function cargarResidentes() {
  // La API pagina de 15 en 15: se piden todas las páginas de residentes activos.
  const todos = []
  let p = 1
  let ultima = 1
  do {
    const r = await api('/residentes', { params: { estado: 'activo', page: p } })
    todos.push(...r.data)
    ultima = r.last_page
    p++
  } while (p <= ultima)
  residentes.value = todos
}

onMounted(async () => {
  cargar()
  try {
    await cargarResidentes()
  } catch (e) {
    error.value = e.message
  }
})

// ---------- nueva cuota ----------
const modalCuota = reactive({ abierto: false })
const formCuota = reactive({})
const erroresCuota = ref({})
const errorCuota = ref('')
const guardando = ref(false)

function abrirNuevaCuota() {
  Object.assign(formCuota, {
    id_residente: '',
    concepto: 'Mantenimiento',
    monto: '',
    periodo: mesActual(),
    fecha_vencimiento: '',
  })
  erroresCuota.value = {}
  errorCuota.value = ''
  modalCuota.abierto = true
}

async function guardarCuota() {
  guardando.value = true
  erroresCuota.value = {}
  errorCuota.value = ''
  try {
    await api('/cuotas', {
      method: 'POST',
      body: {
        id_residente: Number(formCuota.id_residente),
        concepto: formCuota.concepto.trim(),
        monto: Number(formCuota.monto),
        periodo: formCuota.periodo,
        fecha_vencimiento: formCuota.fecha_vencimiento,
      },
    })
    modalCuota.abierto = false
    aviso.value = 'Cuota creada'
    await cargar()
  } catch (e) {
    if (e instanceof ApiError && e.status === 422 && Object.keys(e.errors).length) erroresCuota.value = e.errors
    else errorCuota.value = e.message
  } finally {
    guardando.value = false
  }
}

// ---------- registrar pago ----------
const modalPago = reactive({ abierto: false, cuota: null })
const formPago = reactive({})
const erroresPago = ref({})
const errorPago = ref('')

function abrirPago(c) {
  modalPago.cuota = c
  Object.assign(formPago, { monto_pagado: saldo(c).toFixed(2), fecha_pago: hoy(), metodo_pago: 'efectivo', referencia: '' })
  erroresPago.value = {}
  errorPago.value = ''
  modalPago.abierto = true
}

async function guardarPago() {
  guardando.value = true
  erroresPago.value = {}
  errorPago.value = ''
  try {
    const r = await api(`/cuotas/${modalPago.cuota.id_cuota}/pagos`, {
      method: 'POST',
      body: {
        monto_pagado: Number(formPago.monto_pagado),
        fecha_pago: formPago.fecha_pago,
        metodo_pago: formPago.metodo_pago,
        referencia: formPago.referencia.trim() || null,
      },
    })
    modalPago.abierto = false
    aviso.value = r.mensaje
    await cargar()
  } catch (e) {
    if (e instanceof ApiError && e.status === 422 && Object.keys(e.errors).length) erroresPago.value = e.errors
    else errorPago.value = e.message
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
        <option value="vencida">Vencidas</option>
        <option value="pagada">Pagadas</option>
      </select>
      <input v-model="periodo" class="filtro" type="month" title="Período" />
      <button v-if="periodo" class="btn btn-claro" @click="periodo = ''">Quitar período</button>
      <button class="btn btn-primario" @click="abrirNuevaCuota">+ Nueva cuota</button>
    </div>

    <p v-if="aviso" class="exito" @click="aviso = ''">{{ aviso }}</p>
    <p v-if="error" class="alerta">{{ error }}</p>

    <div class="caja">
      <table>
        <thead>
          <tr>
            <th>Residente</th>
            <th>Unidad</th>
            <th>Concepto</th>
            <th>Período</th>
            <th>Vence</th>
            <th>Monto</th>
            <th>Pagado</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="cargando"><td colspan="9" class="vacio">Cargando…</td></tr>
          <tr v-else-if="!items.length"><td colspan="9" class="vacio">No hay cuotas que coincidan.</td></tr>
          <template v-else>
            <tr v-for="c in items" :key="c.id_cuota">
              <td>{{ c.residente?.nombres }} {{ c.residente?.apellidos }}</td>
              <td>{{ c.residente?.unidad?.numero }}</td>
              <td>{{ c.concepto }}</td>
              <td>{{ c.periodo }}</td>
              <td>{{ c.fecha_vencimiento }}</td>
              <td>{{ dinero(c.monto) }}</td>
              <td>{{ dinero(c.total_pagado) }}</td>
              <td><span class="etiqueta" :class="c.estado">{{ textoEstado[c.estado] }}</span></td>
              <td class="acciones">
                <button v-if="c.estado !== 'pagada'" class="enlace" @click="abrirPago(c)">Registrar pago</button>
                <span v-else class="gris">—</span>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="paginas">
      <span>Página {{ meta.current_page }} de {{ meta.last_page }} · {{ meta.total }} cuotas</span>
      <span>
        <button class="btn btn-claro" :disabled="meta.current_page <= 1" @click="irPagina(meta.current_page - 1)">Anterior</button>
        <button class="btn btn-claro" :disabled="meta.current_page >= meta.last_page" @click="irPagina(meta.current_page + 1)">Siguiente</button>
      </span>
    </div>

    <!-- Nueva cuota -->
    <div v-if="modalCuota.abierto" class="fondo-modal" @click.self="modalCuota.abierto = false">
      <form class="modal" @submit.prevent="guardarCuota">
        <h2>Nueva cuota</h2>
        <div class="campo">
          <label>Residente</label>
          <select v-model="formCuota.id_residente" required>
            <option value="" disabled>Selecciona…</option>
            <option v-for="r in residentes" :key="r.id_residente" :value="r.id_residente">
              {{ r.unidad?.numero }} · {{ r.nombres }} {{ r.apellidos }}
            </option>
          </select>
          <small v-if="erroresCuota.id_residente" class="error">{{ erroresCuota.id_residente[0] }}</small>
        </div>
        <div class="fila">
          <div class="campo">
            <label>Concepto</label>
            <input v-model="formCuota.concepto" required maxlength="100" />
            <small v-if="erroresCuota.concepto" class="error">{{ erroresCuota.concepto[0] }}</small>
          </div>
          <div class="campo">
            <label>Monto ($)</label>
            <input v-model="formCuota.monto" type="number" step="0.01" min="0.01" required />
            <small v-if="erroresCuota.monto" class="error">{{ erroresCuota.monto[0] }}</small>
          </div>
        </div>
        <div class="fila">
          <div class="campo">
            <label>Período</label>
            <input v-model="formCuota.periodo" type="month" required />
            <small v-if="erroresCuota.periodo" class="error">{{ erroresCuota.periodo[0] }}</small>
          </div>
          <div class="campo">
            <label>Fecha de vencimiento</label>
            <input v-model="formCuota.fecha_vencimiento" type="date" required />
            <small v-if="erroresCuota.fecha_vencimiento" class="error">{{ erroresCuota.fecha_vencimiento[0] }}</small>
          </div>
        </div>
        <p v-if="errorCuota" class="alerta">{{ errorCuota }}</p>
        <div class="botones">
          <button type="button" class="btn btn-claro" @click="modalCuota.abierto = false">Cancelar</button>
          <button class="btn btn-primario" :disabled="guardando">{{ guardando ? 'Guardando…' : 'Guardar' }}</button>
        </div>
      </form>
    </div>

    <!-- Registrar pago -->
    <div v-if="modalPago.abierto" class="fondo-modal" @click.self="modalPago.abierto = false">
      <form class="modal" @submit.prevent="guardarPago">
        <h2>Registrar pago</h2>
        <p class="resumen">
          {{ modalPago.cuota.residente?.nombres }} {{ modalPago.cuota.residente?.apellidos }} ·
          {{ modalPago.cuota.concepto }} {{ modalPago.cuota.periodo }}<br />
          Monto {{ dinero(modalPago.cuota.monto) }} · Pagado {{ dinero(modalPago.cuota.total_pagado) }} ·
          <strong>Saldo {{ dinero(saldo(modalPago.cuota)) }}</strong>
        </p>
        <div class="fila">
          <div class="campo">
            <label>Monto a pagar ($)</label>
            <input v-model="formPago.monto_pagado" type="number" step="0.01" min="0.01" required />
            <small v-if="erroresPago.monto_pagado" class="error">{{ erroresPago.monto_pagado[0] }}</small>
          </div>
          <div class="campo">
            <label>Fecha de pago</label>
            <input v-model="formPago.fecha_pago" type="date" :max="hoy()" required />
            <small v-if="erroresPago.fecha_pago" class="error">{{ erroresPago.fecha_pago[0] }}</small>
          </div>
        </div>
        <div class="fila">
          <div class="campo">
            <label>Método</label>
            <select v-model="formPago.metodo_pago">
              <option value="efectivo">Efectivo</option>
              <option value="transferencia">Transferencia</option>
              <option value="cheque">Cheque</option>
            </select>
            <small v-if="erroresPago.metodo_pago" class="error">{{ erroresPago.metodo_pago[0] }}</small>
          </div>
          <div class="campo">
            <label>Referencia (opcional)</label>
            <input v-model="formPago.referencia" maxlength="50" />
            <small v-if="erroresPago.referencia" class="error">{{ erroresPago.referencia[0] }}</small>
          </div>
        </div>
        <p v-if="errorPago" class="alerta">{{ errorPago }}</p>
        <div class="botones">
          <button type="button" class="btn btn-claro" @click="modalPago.abierto = false">Cancelar</button>
          <button class="btn btn-primario" :disabled="guardando">{{ guardando ? 'Guardando…' : 'Registrar pago' }}</button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.barra { display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; align-items: center; }
.filtro { border: 1px solid var(--borde); border-radius: 8px; padding: 10px 12px; background: #fff; }
.barra .btn-primario { margin-left: auto; }
.exito { background: var(--verde-fondo); color: var(--verde); border-radius: 8px; padding: 10px 12px; font-size: 14px; cursor: pointer; }

.caja { background: #fff; border: 1px solid var(--borde); border-radius: 12px; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
th { text-align: left; font-size: 11px; letter-spacing: 0.5px; text-transform: uppercase; color: var(--gris); background: #f8fafc; padding: 12px 14px; white-space: nowrap; }
td { padding: 12px 14px; border-top: 1px solid var(--borde); }
.vacio { text-align: center; color: var(--gris); padding: 32px; }
.gris { color: var(--gris); }

.etiqueta { font-size: 12px; font-weight: 600; border-radius: 999px; padding: 3px 10px; }
.etiqueta.pagada { background: var(--verde-fondo); color: var(--verde); }
.etiqueta.pendiente { background: #fef9c3; color: #854d0e; }
.etiqueta.vencida { background: var(--rojo-fondo); color: var(--rojo); }

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
