import type { Routes } from '@angular/router';
import { autenticadoGuard, invitadoGuard } from './core/auth.guard';

export const routes: Routes = [
  {
    path: 'login',
    canActivate: [invitadoGuard],
    loadComponent: () => import('./login/login.page').then((m) => m.LoginPage),
  },
  {
    path: 'inicio',
    canActivate: [autenticadoGuard],
    loadComponent: () => import('./inicio/inicio.page').then((m) => m.InicioPage),
  },
  { path: '', redirectTo: 'inicio', pathMatch: 'full' },
  { path: '**', redirectTo: 'inicio' },
];
