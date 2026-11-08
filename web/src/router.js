import { createRouter, createWebHistory } from 'vue-router'
import { session } from './session'

const proximamente = (titulo) => ({
  component: () => import('./views/ProximamenteView.vue'),
  meta: { titulo },
})

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', component: () => import('./views/LoginView.vue'), meta: { invitado: true } },
    {
      path: '/',
      component: () => import('./layouts/PanelLayout.vue'),
      meta: { requiereSesion: true },
      redirect: '/residentes',
      children: [
        { path: 'dashboard', ...proximamente('Dashboard') },
        { path: 'residentes', component: () => import('./views/ResidentesView.vue'), meta: { titulo: 'Gestión de residentes' } },
        { path: 'cuotas', ...proximamente('Cuotas y pagos') },
        { path: 'quejas', ...proximamente('Quejas') },
        { path: 'mantenimiento', ...proximamente('Mantenimiento') },
        { path: 'personal', ...proximamente('Personal') },
        { path: 'ingresos', ...proximamente('Ingresos') },
      ],
    },
    { path: '/:cualquiera(.*)*', redirect: '/' },
  ],
})

router.beforeEach((to) => {
  if (to.matched.some((r) => r.meta.requiereSesion) && !session.token) return '/login'
  if (to.meta.invitado && session.token) return '/residentes'
})

export default router
