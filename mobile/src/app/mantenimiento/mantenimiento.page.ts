import { Component, OnInit, inject, signal } from '@angular/core';
import { Router } from '@angular/router';
import { IonContent } from '@ionic/angular';
import { ApiError, ApiService } from '../core/api.service';
import type { EstadoReporte, SolicitudMantenimiento } from '../core/modelos';

@Component({
  selector: 'app-mantenimiento',
  templateUrl: 'mantenimiento.page.html',
  styleUrls: ['../core/pantalla.scss'],
  imports: [IonContent],
})
export class MantenimientoPage implements OnInit {
  private readonly api = inject(ApiService);
  private readonly router = inject(Router);

  readonly solicitudes = signal<SolicitudMantenimiento[]>([]);
  readonly cargando = signal(true);
  readonly error = signal('');
  readonly aviso = signal('');

  // formulario
  readonly formAbierto = signal(false);
  readonly titulo = signal('');
  readonly ubicacion = signal('');
  readonly prioridad = signal('media');
  readonly descripcion = signal('');
  readonly enviando = signal(false);
  readonly errorForm = signal('');

  readonly prioridades = [
    { valor: 'baja', texto: 'Baja' },
    { valor: 'media', texto: 'Media' },
    { valor: 'alta', texto: 'Alta' },
  ];
  readonly textoEstado: Record<EstadoReporte, string> = {
    pendiente: 'Pendiente',
    en_proceso: 'En proceso',
    resuelta: 'Resuelta',
    completada: 'Completada',
  };

  async ngOnInit(): Promise<void> {
    await this.cargar();
  }

  private async cargar(): Promise<void> {
    try {
      this.solicitudes.set(await this.api.request<SolicitudMantenimiento[]>('/mis-solicitudes'));
    } catch (e) {
      if (e instanceof ApiError && e.estado !== 401) {
        this.error.set(e.message);
      }
    } finally {
      this.cargando.set(false);
    }
  }

  texto(valor: string): string {
    return this.prioridades.find((p) => p.valor === valor)?.texto ?? valor;
  }

  abrirForm(): void {
    this.titulo.set('');
    this.ubicacion.set('');
    this.prioridad.set('media');
    this.descripcion.set('');
    this.errorForm.set('');
    this.aviso.set('');
    this.formAbierto.set(true);
  }

  async enviar(): Promise<void> {
    if (this.enviando()) {
      return;
    }
    this.errorForm.set('');
    if (!this.titulo().trim() || !this.ubicacion().trim() || !this.descripcion().trim()) {
      this.errorForm.set('Completa el título, la ubicación y la descripción');
      return;
    }
    this.enviando.set(true);
    try {
      await this.api.request('/mis-solicitudes', {
        method: 'POST',
        body: {
          titulo: this.titulo().trim(),
          ubicacion: this.ubicacion().trim(),
          prioridad: this.prioridad(),
          descripcion: this.descripcion().trim(),
        },
      });
      this.formAbierto.set(false);
      this.aviso.set('Tu solicitud fue enviada a la administración');
      await this.cargar();
    } catch (e) {
      this.errorForm.set(e instanceof ApiError ? e.detalle : 'Ocurrió un error inesperado');
    } finally {
      this.enviando.set(false);
    }
  }

  async volver(): Promise<void> {
    await this.router.navigateByUrl('/inicio');
  }
}
