import { Injectable, inject } from '@angular/core';
import { ApiError, ApiService } from './api.service';
import type { RespuestaLogin, Usuario } from './modelos';
import { SessionService } from './session.service';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly api = inject(ApiService);
  private readonly session = inject(SessionService);

  async login(correo: string, password: string): Promise<void> {
    const r = await this.api.request<RespuestaLogin>('/login', {
      method: 'POST',
      body: { correo, password },
    });

    this.session.guardar(r.token, r.usuario);

    if (r.usuario.rol !== 'residente') {
      // Esta app es solo para residentes: se invalida el token recién creado.
      try {
        await this.api.request('/logout', { method: 'POST' });
      } catch {
        // da igual: igual se limpia la sesión local
      }
      this.session.limpiar();
      throw new ApiError(403, {
        mensaje: 'Esta app es para residentes. Los administradores usan el panel web.',
      });
    }
  }

  async logout(): Promise<void> {
    try {
      await this.api.request('/logout', { method: 'POST' });
    } catch {
      // si el token ya no servía, no pasa nada
    }
    this.session.limpiar();
  }

  async me(): Promise<Usuario> {
    const usuario = await this.api.request<Usuario>('/me');
    this.session.actualizarUsuario(usuario);
    return usuario;
  }
}
