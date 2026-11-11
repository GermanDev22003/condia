import { inject } from '@angular/core';
import type { CanActivateFn } from '@angular/router';
import { Router } from '@angular/router';
import { SessionService } from './session.service';

/** Solo deja pasar si hay sesión; si no, manda al login. */
export const autenticadoGuard: CanActivateFn = () => {
  const session = inject(SessionService);
  const router = inject(Router);
  return session.token() ? true : router.createUrlTree(['/login']);
};

/** Para el login: si ya hay sesión, manda al inicio. */
export const invitadoGuard: CanActivateFn = () => {
  const session = inject(SessionService);
  const router = inject(Router);
  return session.token() ? router.createUrlTree(['/inicio']) : true;
};
