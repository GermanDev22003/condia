import { api, ApiError } from './api'
import { iniciarSesion, cerrarSesionLocal } from './session'

export async function login(correo, password) {
  const r = await api('/login', { method: 'POST', body: { correo, password } })

  iniciarSesion(r.token, r.usuario)

  if (r.usuario.rol !== 'administrador') {
    // Este panel es solo para administradores: se invalida el token recién creado.
    try {
      await api('/logout', { method: 'POST' })
    } catch {
      // da igual: igual se limpia la sesión local
    }
    cerrarSesionLocal()
    throw new ApiError(403, {
      mensaje: 'Este panel es solo para administradores. Los residentes usan la app móvil.',
    })
  }
}

export async function logout() {
  try {
    await api('/logout', { method: 'POST' })
  } catch {
    // si el token ya no servía, no pasa nada
  }
  cerrarSesionLocal()
}
