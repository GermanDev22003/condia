import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { setManejadorNoAutorizado } from './api'
import { cerrarSesionLocal } from './session'
import './assets/main.css'

// Si la API responde 401 (token vencido o invalidado), volvemos al login.
setManejadorNoAutorizado(() => {
  cerrarSesionLocal()
  router.push('/login')
})

createApp(App).use(router).mount('#app')
