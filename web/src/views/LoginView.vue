<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { login } from '../auth'
import CampoPassword from '../components/CampoPassword.vue'

const router = useRouter()
const correo = ref('')
const password = ref('')
const error = ref('')
const enviando = ref(false)

async function ingresar() {
  error.value = ''
  enviando.value = true
  try {
    await login(correo.value.trim(), password.value)
    router.push('/dashboard')
  } catch (e) {
    const detalle = e.errors && Object.values(e.errors)[0]?.[0]
    error.value = detalle || e.message
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <main class="fondo">
    <form class="tarjeta" @submit.prevent="ingresar">
      <div class="logo">Con<span>DIA</span></div>
      <h1>Iniciar sesión</h1>
      <p class="sub">Panel administrativo del condominio</p>

      <div class="campo">
        <label for="correo">Correo electrónico</label>
        <input id="correo" v-model="correo" type="email" placeholder="admin@condia.test" required autofocus />
      </div>
      <div class="campo">
        <label for="password">Contraseña</label>
        <CampoPassword id="password" v-model="password" placeholder="••••••••" autocomplete="current-password" required />
      </div>

      <p v-if="error" class="alerta">{{ error }}</p>

      <button class="btn btn-primario ancho" :disabled="enviando">
        {{ enviando ? 'Ingresando…' : 'Ingresar' }}
      </button>
    </form>
  </main>
</template>

<style scoped>
.fondo {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 16px;
  background: linear-gradient(135deg, #0f172a 0%, #115e59 100%);
}
.tarjeta {
  background: #fff;
  width: 100%;
  max-width: 400px;
  border-radius: 16px;
  padding: 40px;
}
h1 { margin: 18px 0 4px; font-size: 26px; }
.sub { margin: 0 0 22px; color: var(--gris); font-size: 13px; }
.ancho { width: 100%; margin-top: 6px; }
.alerta { margin: 0 0 12px; }
</style>
