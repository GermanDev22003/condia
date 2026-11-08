import { Component, OnInit, computed, inject, signal } from '@angular/core';
import { Router } from '@angular/router';
import { IonContent } from '@ionic/angular';
import { ApiError, ApiService } from '../core/api.service';
import { AuthService } from '../core/auth.service';
import type { MisCuotas, Usuario } from '../core/modelos';
import { SessionService } from '../core/session.service';

@Component({
  selector: 'app-inicio',
  templateUrl: 'inicio.page.html',
  styleUrls: ['inicio.page.scss'],
  imports: [IonContent],
})
export class InicioPage implements OnInit {
  private readonly api = inject(ApiService);
  private readonly auth = inject(AuthService);
  private readonly session = inject(SessionService);
  private readonly router = inject(Router);

  // Se muestra primero lo guardado en la sesión y luego se actualiza con la API.
  readonly perfil = signal<Usuario | null>(this.session.usuario());
  readonly error = signal('');
  readonly pendiente = signal<string | null>(null);

  readonly nombre = computed(() => this.perfil()?.residente?.nombres.split(' ')[0] ?? '');
  readonly lugar = computed(() => {
    const residente = this.perfil()?.residente;
    if (!residente) {
      return '';
    }
    const condominio = residente.unidad.condominio?.nombre ?? 'ConDIA';
    return `${condominio} · ${residente.unidad.numero}`;
  });

  async ngOnInit(): Promise<void> {
    try {
      this.perfil.set(await this.auth.me());
      const cuotas = await this.api.request<MisCuotas>('/mis-cuotas');
      this.pendiente.set(cuotas.pendiente_total);
    } catch (e) {
      if (e instanceof ApiError && e.estado !== 401) {
        this.error.set(e.message);
      }
    }
  }

  async verCuotas(): Promise<void> {
    await this.router.navigateByUrl('/cuotas');
  }

  async salir(): Promise<void> {
    await this.auth.logout();
    await this.router.navigateByUrl('/login', { replaceUrl: true });
  }
}
