<template>
  <div class="dashboard">
    <header class="dashboard-header">
      <h1>Панель управления</h1>
      <button @click="logout" class="btn-logout">Выйти</button>
    </header>
    <div v-if="authStore.isAuthenticated">
      <DispatcherDashboard v-if="authStore.role === 'dispatcher'" />
      <MasterDashboard v-else-if="authStore.role === 'master'" />
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import DispatcherDashboard from '@/components/DispatcherDashboard.vue';
import MasterDashboard from '@/components/MasterDashboard.vue';

const authStore = useAuthStore();
const router = useRouter();

const logout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
.dashboard {
  max-width: 1300px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
  background: #f5f0e8;
  min-height: 100vh;
}
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2.5rem;
}
h1 {
  color: #535874;
  font-weight: 500;
  font-size: 2rem;
}
.btn-logout {
  background: #d0c8c0;
  color: #535874;
  border: none;
  padding: 0.6rem 1.8rem;
  border-radius: 30px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  box-shadow: 0 4px 6px rgba(0,0,0,0.08);
  transition: background 0.2s;
}
.btn-logout:hover {
  background: #b8b0a8;
}
</style>