<script setup>
import { ref, onMounted } from 'vue'
import { api } from '../api'

const dinero = (n) => '$' + Number(n || 0).toFixed(2)
const iso = (d) => d.toISOString().slice(0, 10)
const hoy = new Date()

const desde = ref(iso(new Date(hoy.getFullYear(), hoy.getMonth(), 1)))
const hasta = ref(iso(hoy))
const resultado = ref(null)
const cargando = ref(false)
const error = ref('')

async function consultar() {
  error.value = ''
  if (desde.value > hasta.value) {
    error.value = 'La fecha final no puede ser menor que la inicial'
    return
  }
  cargando.value = true
  try {
    resultado.value = await api('/ingresos', { params: { desde: desde.value, hasta: hasta.value } })
  } catch (e) {
    error.value = e.message
  } finally {
    cargando.value = false
  }
}

onMounted(consultar)
</script>

<template>
  <div>
    <form class="barra" @submit.prevent="consultar">
      <div class="campo">
        <label>Desde</label>
        <input v-model="desde" type="date" required />
      </div>
      <div class="campo">
        <label>Hasta</label>
        <input v-model="hasta" type="date" required />
      </div>
      <button class="btn btn-primario" :disabled="cargando">{{ cargando ? 'Consultando…' : 'Consultar' }}</button>
    </form>

    <p v-if="error" class="alerta">{{ error }}</p>

    <template v-if="resultado">
      <div class="resumen">
        <div class="tarjeta">
          <span>Total recaudado</span>
          <strong>{{ dinero(resultado.total) }}</strong>
        </div>
        <div class="tarjeta">
          <span>Pagos registrados</span>
          <strong>{{ resultado.cantidad_pagos }}</strong>
        </div>
      </div>

      <div class="caja">
        <table>
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Residente</th>
              <th>Concepto</th>
              <th>Método</th>
              <th>Referencia</th>
              <th>Monto</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!resultado.pagos.length"><td colspan="6" class="vacio">No hay pagos en el rango elegido.</td></tr>
            <tr v-for="p in resultado.pagos" :key="p.id_pago">
              <td>{{ p.fecha_pago }}</td>
              <td>{{ p.cuota?.residente?.nombres }} {{ p.cuota?.residente?.apellidos }}</td>
              <td>{{ p.cuota?.concepto }} {{ p.cuota?.periodo }}</td>
              <td>{{ p.metodo_pago }}</td>
              <td>{{ p.referencia || '—' }}</td>
              <td>{{ dinero(p.monto_pagado) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>

<style scoped>
.barra { display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 8px; }
.barra .campo { margin-bottom: 0; }
.barra .btn { margin-bottom: 0; }
.resumen { display: flex; gap: 16px; margin: 16px 0; flex-wrap: wrap; }
.tarjeta { background: #fff; border: 1px solid var(--borde); border-radius: 12px; padding: 16px 22px; min-width: 200px; display: flex; flex-direction: column; gap: 4px; }
.tarjeta span { color: var(--gris); font-size: 13px; }
.tarjeta strong { font-size: 26px; color: var(--teal); }
.caja { background: #fff; border: 1px solid var(--borde); border-radius: 12px; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
th { text-align: left; font-size: 11px; letter-spacing: 0.5px; text-transform: uppercase; color: var(--gris); background: #f8fafc; padding: 12px 16px; }
td { padding: 13px 16px; border-top: 1px solid var(--borde); }
.vacio { text-align: center; color: var(--gris); padding: 32px; }
</style>
