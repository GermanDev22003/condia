import { Component, inject, signal } from '@angular/core';
import { Router } from '@angular/router';
import { IonContent } from '@ionic/angular';
import { ApiError, ApiService } from '../core/api.service';

@Component({
  selector: 'app-cambiar-password',
  templateUrl: 'cambiar-password.page.html',
  styleUrls: ['../core/pantalla.scss'],
  imports: [IonContent],
})
export class CambiarPasswordPage {
  private readonly api = inject(ApiService);
  private readonly router = inject(Router);

  readonly actual = signal('');
  readonly nueva = signal('');
  readonly confirmacion = signal('');
  readonly ver = signal(false);
  readonly enviando = signal(false);
  readonly error = signal('');
  readonly exito = signal(false);

  async guardar(): Promise<void> {
    if (this.enviando()) {
      return;
    }
    this.error.set('');
    if (!this.actual() || !this.nueva() || !this.confirmacion()) {
      this.error.set('Completa los tres campos');
      return;
    }
    if (this.nueva().length < 8) {
      this.error.set('La nueva contraseña debe tener al menos 8 caracteres');
      return;
    }
    if (this.nueva() !== this.confirmacion()) {
      this.error.set('La confirmación no coincide con la nueva contraseña');
      return;
    }
    this.enviando.set(true);
    try {
      await this.api.request('/cambiar-password', {
        method: 'POST',
        body: {
          password_actual: this.actual(),
          password: this.nueva(),
          password_confirmation: this.confirmacion(),
        },
      });
      this.actual.set('');
      this.nueva.set('');
      this.confirmacion.set('');
      this.exito.set(true);
    } catch (e) {
      this.error.set(e instanceof ApiError ? e.detalle : 'Ocurrió un error inesperado');
    } finally {
      this.enviando.set(false);
    }
  }

  async volver(): Promise<void> {
    await this.router.navigateByUrl('/inicio');
  }
}
