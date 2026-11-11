import { session } from './session'

const BASE = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'

export class ApiError extends Error {
  constructor(status, datos) {
    super(datos?.mensaje || datos?.message || 'Ocurrió un error inesperado')
    this.status = status
    this.errors = datos?.errors || {}
  }
}

let alNoAutorizado = () => {}
export function setManejadorNoAutorizado(fn) {
  alNoAutorizado = fn
}

/**
 * Llama a la API de Laravel.
 *   api('/residentes', { params: { buscar: 'ana' } })
 *   api('/residentes', { method: 'POST', body: { ... } })
 */
export async function api(ruta, { method = 'GET', body, params } = {}) {
  const url = new URL(BASE + ruta)
  if (params) {
    for (const [clave, valor] of Object.entries(params)) {
      if (valor !== '' && valor !== null && valor !== undefined) url.searchParams.set(clave, valor)
    }
  }

  const headers = { Accept: 'application/json' }
  if (session.token) headers.Authorization = `Bearer ${session.token}`
  if (body) headers['Content-Type'] = 'application/json'

  let respuesta
  try {
    respuesta = await fetch(url, { method, headers, body: body ? JSON.stringify(body) : undefined })
  } catch {
    throw new ApiError(0, {
      mensaje: 'No se pudo conectar con la API. ¿Está corriendo "php artisan serve"?',
    })
  }

  const datos = await respuesta.json().catch(() => null)

  if (respuesta.status === 401 && session.token) alNoAutorizado()
  if (!respuesta.ok) throw new ApiError(respuesta.status, datos)

  return datos
}
