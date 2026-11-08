import { reactive } from 'vue'

function leer(clave) {
  try {
    return localStorage.getItem(clave)
  } catch {
    return null
  }
}

function escribir(clave, valor) {
  try {
    if (valor === null) localStorage.removeItem(clave)
    else localStorage.setItem(clave, valor)
  } catch {
    // sin localStorage la sesión solo dura mientras la pestaña esté abierta
  }
}

export const session = reactive({
  token: leer('condia_token'),
  usuario: JSON.parse(leer('condia_usuario') || 'null'),
})

export function iniciarSesion(token, usuario) {
  session.token = token
  session.usuario = usuario
  escribir('condia_token', token)
  escribir('condia_usuario', JSON.stringify(usuario))
}

export function cerrarSesionLocal() {
  session.token = null
  session.usuario = null
  escribir('condia_token', null)
  escribir('condia_usuario', null)
}
