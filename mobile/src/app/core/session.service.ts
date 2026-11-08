import { Injectable, signal } from '@angular/core';
import type { Usuario } from './modelos';

function leer(clave: string): string | null {
  try {
    return localStorage.getItem(clave);
  } catch {
    return null;
  }
}

function escribir(clave: string, valor: string | null): void {
  try {
    if (valor === null) {
      localStorage.removeItem(clave);
    } else {
      localStorage.setItem(clave, valor);
    }
  } catch {
    // sin localStorage la sesión solo dura mientras la app esté abierta
  }
}

/** Guarda el token y el usuario de la sesión actual. */
@Injectable({ providedIn: 'root' })
export class SessionService {
  readonly token = signal<string | null>(leer('condia_token'));
  readonly usuario = signal<Usuario | null>(JSON.parse(leer('condia_usuario') ?? 'null') as Usuario | null);

  guardar(token: string, usuario: Usuario): void {
    this.token.set(token);
    this.usuario.set(usuario);
    escribir('condia_token', token);
    escribir('condia_usuario', JSON.stringify(usuario));
  }

  actualizarUsuario(usuario: Usuario): void {
    this.usuario.set(usuario);
    escribir('condia_usuario', JSON.stringify(usuario));
  }

  limpiar(): void {
    this.token.set(null);
    this.usuario.set(null);
    escribir('condia_token', null);
    escribir('condia_usuario', null);
  }
}
