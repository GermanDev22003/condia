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
  {
    path: 'cuotas',
    canActivate: [autenticadoGuard],
    loadComponent: () => import('./cuotas/cuotas.page').then((m) => m.CuotasPage),
  },
  {
    path: 'quejas',
    canActivate: [autenticadoGuard],
    loadComponent: () => import('./quejas/quejas.page').then((m) => m.QuejasPage),
  },
  {
    path: 'mantenimiento',
    canActivate: [autenticadoGuard],
    loadComponent: () => import('./mantenimiento/mantenimiento.page').then((m) => m.MantenimientoPage),
  },
  { path: '', redirectTo: 'inicio', pathMatch: 'full' },
  { path: '**', redirectTo: 'inicio' },
];
