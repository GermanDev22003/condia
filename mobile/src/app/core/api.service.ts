import { Injectable, inject } from '@angular/core';
import { Router } from '@angular/router';
import { environment } from '../../environments/environment';
import { SessionService } from './session.service';

interface CuerpoError {
  mensaje?: string;
  message?: string;
  errors?: Record<string, string[]>;
}

export class ApiError extends Error {
  readonly estado: number;
  readonly errors: Record<string, string[]>;

  constructor(estado: number, cuerpo: CuerpoError | null) {
    super(cuerpo?.mensaje ?? cuerpo?.message ?? 'Ocurrió un error inesperado');
    this.estado = estado;
    this.errors = cuerpo?.errors ?? {};
  }

  /** Primer mensaje de validación (por ejemplo "El correo no es válido") o el mensaje general. */
  get detalle(): string {
    const primero = Object.values(this.errors)[0]?.[0];
    return primero ?? this.message;
  }
}

/** Llama a la API de Laravel con el token de la sesión. */
@Injectable({ providedIn: 'root' })
export class ApiService {
  private readonly session = inject(SessionService);
  private readonly router = inject(Router);

  async request<T>(ruta: string, opciones: { method?: string; body?: unknown } = {}): Promise<T> {
    const headers: Record<string, string> = { Accept: 'application/json' };
    const token = this.session.token();
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }
    if (opciones.body !== undefined) {
      headers['Content-Type'] = 'application/json';
    }

    let respuesta: Response;
    try {
      respuesta = await fetch(environment.apiUrl + ruta, {
        method: opciones.method ?? 'GET',
        headers,
        body: opciones.body !== undefined ? JSON.stringify(opciones.body) : undefined,
      });
    } catch {
      throw new ApiError(0, { mensaje: 'No se pudo conectar con el servidor. Revisa tu conexión.' });
    }

    const cuerpo = (await respuesta.json().catch(() => null)) as unknown;

    if (respuesta.status === 401 && token) {
      // token vencido o invalidado: volvemos al login
      this.session.limpiar();
      await this.router.navigateByUrl('/login', { replaceUrl: true });
    }
    if (!respuesta.ok) {
      throw new ApiError(respuesta.status, cuerpo as CuerpoError | null);
    }
    return cuerpo as T;
  }
}
