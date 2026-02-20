import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth';

const routes = [
  {
    path: '/login',
    component: () => import('@/views/Login.vue'),
    meta: { guest: true }
  },
  { path: '/', redirect: '/dashboard' },
  {
    path: '/dashboard',
    component: () => import('@/views/Dashboard.vue'),
    meta: { requiresAuth: true }
  },
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach(async (to, from) => {
  const authStore = useAuthStore();

  if (!authStore.user) {
    await authStore.fetchUser();
  }


  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return '/login';
  } else if (to.meta.guest && authStore.isAuthenticated) {
    return '/dashboard';
  } else if (to.meta.role && authStore.role !== to.meta.role) {
    return '/dashboard';
  } else {
    return true;
  }
});

export default router
