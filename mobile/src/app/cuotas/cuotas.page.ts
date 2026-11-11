import { Component, OnInit, inject, signal } from '@angular/core';
import { Router } from '@angular/router';
import { IonContent } from '@ionic/angular';
import { ApiError, ApiService } from '../core/api.service';
import type { Cuota, EstadoCuota, MisCuotas } from '../core/modelos';

@Component({
  selector: 'app-cuotas',
  templateUrl: 'cuotas.page.html',
  styleUrls: ['cuotas.page.scss'],
  imports: [IonContent],
})
export class CuotasPage implements OnInit {
  private readonly api = inject(ApiService);
  private readonly router = inject(Router);

  readonly datos = signal<MisCuotas | null>(null);
  readonly cargando = signal(true);
  readonly error = signal('');
  /** Cuota cuyo detalle de pagos está abierto. */
  readonly abierta = signal<number | null>(null);

  readonly textoEstado: Record<EstadoCuota, string> = {
    pendiente: 'Pendiente',
    pagada: 'Pagada',
    vencida: 'Vencida',
  };

  async ngOnInit(): Promise<void> {
    try {
      this.datos.set(await this.api.request<MisCuotas>('/mis-cuotas'));
    } catch (e) {
      if (e instanceof ApiError && e.estado !== 401) {
        this.error.set(e.message);
      }
    } finally {
      this.cargando.set(false);
    }
  }

  saldo(c: Cuota): number {
    return Number(c.monto) - Number(c.total_pagado ?? 0);
  }

  alternar(c: Cuota): void {
    this.abierta.set(this.abierta() === c.id_cuota ? null : c.id_cuota);
  }

  async volver(): Promise<void> {
    await this.router.navigateByUrl('/inicio');
  }
}
