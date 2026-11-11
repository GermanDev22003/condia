import { Component, OnInit, inject, signal } from '@angular/core';
import { Router } from '@angular/router';
import { IonContent } from '@ionic/angular';
import { ApiError, ApiService } from '../core/api.service';
import type { EstadoReporte, Queja } from '../core/modelos';

@Component({
  selector: 'app-quejas',
  templateUrl: 'quejas.page.html',
  styleUrls: ['../core/pantalla.scss'],
  imports: [IonContent],
})
export class QuejasPage implements OnInit {
  private readonly api = inject(ApiService);
  private readonly router = inject(Router);

  readonly quejas = signal<Queja[]>([]);
  readonly cargando = signal(true);
  readonly error = signal('');
  readonly aviso = signal('');

  // formulario
  readonly formAbierto = signal(false);
  readonly asunto = signal('');
  readonly categoria = signal('convivencia');
  readonly descripcion = signal('');
  readonly enviando = signal(false);
  readonly errorForm = signal('');

  readonly categorias: { valor: string; texto: string }[] = [
    { valor: 'convivencia', texto: 'Convivencia' },
    { valor: 'limpieza', texto: 'Limpieza' },
    { valor: 'seguridad', texto: 'Seguridad' },
    { valor: 'ruido', texto: 'Ruido' },
    { valor: 'infraestructura', texto: 'Infraestructura' },
    { valor: 'otra', texto: 'Otra' },
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
      this.quejas.set(await this.api.request<Queja[]>('/mis-quejas'));
    } catch (e) {
      if (e instanceof ApiError && e.estado !== 401) {
        this.error.set(e.message);
      }
    } finally {
      this.cargando.set(false);
    }
  }

  texto(valor: string): string {
    return this.categorias.find((c) => c.valor === valor)?.texto ?? valor;
  }

  abrirForm(): void {
    this.asunto.set('');
    this.categoria.set('convivencia');
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
    if (!this.asunto().trim() || !this.descripcion().trim()) {
      this.errorForm.set('Escribe el asunto y la descripción');
      return;
    }
    this.enviando.set(true);
    try {
      await this.api.request('/mis-quejas', {
        method: 'POST',
        body: { asunto: this.asunto().trim(), categoria: this.categoria(), descripcion: this.descripcion().trim() },
      });
      this.formAbierto.set(false);
      this.aviso.set('Tu queja fue enviada a la administración');
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
