import { Component, inject, signal } from '@angular/core';
import { Router } from '@angular/router';
import { IonButton, IonContent, IonInput, IonInputPasswordToggle } from '@ionic/angular';
import { ApiError } from '../core/api.service';
import { AuthService } from '../core/auth.service';

@Component({
  selector: 'app-login',
  templateUrl: 'login.page.html',
  styleUrls: ['login.page.scss'],
  imports: [IonContent, IonInput, IonInputPasswordToggle, IonButton],
})
export class LoginPage {
  private readonly auth = inject(AuthService);
  private readonly router = inject(Router);

  readonly correo = signal('');
  readonly password = signal('');
  readonly error = signal('');
  readonly enviando = signal(false);

  async ingresar(): Promise<void> {
    if (this.enviando()) {
      return;
    }
    this.error.set('');

    if (!this.correo().trim() || !this.password()) {
      this.error.set('Escribe tu correo y tu contraseña');
      return;
    }

    this.enviando.set(true);
    try {
      await this.auth.login(this.correo().trim(), this.password());
      await this.router.navigateByUrl('/inicio', { replaceUrl: true });
    } catch (e) {
      this.error.set(e instanceof ApiError ? e.detalle : 'Ocurrió un error inesperado');
    } finally {
      this.enviando.set(false);
    }
  }
}
