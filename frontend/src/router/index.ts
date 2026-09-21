import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/Login.vue'),
      meta: { guestOnly: true, layout: 'blank' },
    },
    {
      path: '/puskesmas/dashboard',
      name: 'puskesmas.dashboard',
      component: () => import('../views/puskesmas/Dashboard.vue'),
      meta: { role: 'puskesmas' },
    },
    {
      path: '/puskesmas/kuesioner',
      name: 'puskesmas.kuesioner',
      component: () => import('../views/puskesmas/Kuesioner.vue'),
      meta: { role: 'puskesmas' },
    },
    {
      path: '/puskesmas/kuesioner/riwayat',
      name: 'puskesmas.kuesioner.riwayat',
      component: () => import('../views/puskesmas/KuesionerRiwayat.vue'),
      meta: { role: 'puskesmas' },
    },
    {
      path: '/puskesmas/kuesioner/riwayat/:periode',
      name: 'puskesmas.kuesioner.detail',
      component: () => import('../views/puskesmas/KuesionerDetail.vue'),
      meta: { role: 'puskesmas' },
    },
    {
      path: '/puskesmas/observasi',
      name: 'puskesmas.observasi',
      component: () => import('../views/puskesmas/Observasi.vue'),
      meta: { role: 'puskesmas' },
    },
    {
      path: '/puskesmas/observasi/riwayat',
      name: 'puskesmas.observasi.riwayat',
      component: () => import('../views/puskesmas/ObservasiRiwayat.vue'),
      meta: { role: 'puskesmas' },
    },
    {
      path: '/puskesmas/observasi/riwayat/:periode',
      name: 'puskesmas.observasi.detail',
      component: () => import('../views/puskesmas/ObservasiDetail.vue'),
      meta: { role: 'puskesmas' },
    },
    {
      path: '/dinas/dashboard',
      name: 'dinas.dashboard',
      component: () => import('../views/dinas/Dashboard.vue'),
      meta: { role: 'dinas' },
    },
    {
      path: '/dinas/puskesmas',
      name: 'dinas.puskesmas',
      component: () => import('../views/dinas/PuskesmasList.vue'),
      meta: { role: 'dinas' },
    },
    {
      path: '/dinas/puskesmas/baru',
      name: 'dinas.puskesmas.baru',
      component: () => import('../views/dinas/PuskesmasForm.vue'),
      meta: { role: 'dinas' },
    },
    {
      path: '/dinas/puskesmas/:id',
      name: 'dinas.puskesmas.detail',
      component: () => import('../views/dinas/PuskesmasDetail.vue'),
      meta: { role: 'dinas' },
    },
    {
      path: '/dinas/puskesmas/:id/edit',
      name: 'dinas.puskesmas.edit',
      component: () => import('../views/dinas/PuskesmasForm.vue'),
      meta: { role: 'dinas' },
    },
    {
      path: '/dinas/puskesmas/:id/kuesioner/:periode',
      name: 'dinas.puskesmas.kuesioner',
      component: () => import('../views/dinas/QuestionnaireDetail.vue'),
      meta: { role: 'dinas' },
    },
    {
      path: '/dinas/puskesmas/:id/observasi/:periode',
      name: 'dinas.puskesmas.observasi',
      component: () => import('../views/dinas/ObservationDetail.vue'),
      meta: { role: 'dinas' },
    },
    {
      path: '/',
      redirect: '/login',
    },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (!auth.initialized) {
    await auth.fetchUser()
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return auth.isDinas ? '/dinas/dashboard' : '/puskesmas/dashboard'
  }

  if (to.meta.role && !auth.isAuthenticated) {
    return '/login'
  }

  if (to.meta.role && to.meta.role !== auth.user?.role) {
    return auth.isDinas ? '/dinas/dashboard' : '/puskesmas/dashboard'
  }

  return true
})

export default router
