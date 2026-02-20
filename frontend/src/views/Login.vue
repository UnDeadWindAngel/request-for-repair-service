<template>
  <div class="login-container">
    <div class="login-card">
      <h2>Вход в систему</h2>
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="email">Email</label>
          <input
              id="email"
              v-model="email"
              type="email"
              required
              placeholder="Введите email"
          />
        </div>
        <div class="form-group">
          <label for="password">Пароль</label>
          <input
              id="password"
              v-model="password"
              type="password"
              required
              placeholder="Введите пароль"
          />
        </div>
        <button type="submit" class="btn btn-primary">Авторизоваться</button>
      </form>

      <div class="demo-accounts">
        <button @click="toggleDemo" class="btn btn-secondary">Демонстрационные аккаунты</button>
        <div v-if="showDemo" class="dropdown">
          <button
              v-for="account in demoAccounts"
              :key="account.email"
              @click="quickLogin(account.email)"
              class="dropdown-item"
          >
            {{ account.email }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const email = ref('');
const password = ref('password'); // для демо
const showDemo = ref(false);

const demoAccounts = [
  { email: 'dispatcher1@example.com' },
  { email: 'dispatcher2@example.com' },
  { email: 'master1@example.com' },
  { email: 'master2@example.com' },
];

const toggleDemo = () => {
  showDemo.value = !showDemo.value;
};

const handleLogin = async () => {
  try {
    await authStore.login({ email: email.value, password: password.value });
    router.push('/dashboard');
  } catch (error) {
    window.$toast?.(error.response?.data?.message || 'Login failed', 'error');
  }
};

const quickLogin = async (demoEmail) => {
  email.value = demoEmail;
  password.value = 'password';
  await handleLogin();
};
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: #f5f0e8; /* тёплый серебристый фон */
}
.login-card {
  background: #e0d8d0; /* чуть темнее фона */
  padding: 2.5rem 2rem;
  border-radius: 24px;
  box-shadow: 0 8px 16px rgba(0,0,0,0.1);
  width: 100%;
  max-width: 400px;
}
h2 {
  text-align: center;
  color: #535874;
  margin-bottom: 2rem;
  font-weight: 500;
}
.form-group {
  margin-bottom: 1.5rem;
}
label {
  display: block;
  margin-bottom: 0.5rem;
  color: #535874;
  font-weight: 500;
  font-size: 0.95rem;
}
input {
  width: 100%;
  padding: 0.9rem 1rem;
  border: 1px solid #d0c8c0;
  border-radius: 18px;
  background: white;
  font-size: 1rem;
  box-sizing: border-box;
  transition: border-color 0.2s;
}
input:focus {
  outline: none;
  border-color: #535874;
}
.btn {
  width: 100%;
  padding: 0.9rem;
  border: none;
  border-radius: 24px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s, box-shadow 0.2s;
  box-shadow: 0 4px 6px rgba(0,0,0,0.08);
  margin-bottom: 0.5rem;
}
.btn-primary {
  background: #c0b8b0;
  color: #535874;
}
.btn-primary:hover {
  background: #a8a098;
}
.btn-secondary {
  background: #d0c8c0;
  color: #535874;
}
.btn-secondary:hover {
  background: #b8b0a8;
}
.demo-accounts {
  position: relative;
  margin-top: 1.5rem;
}
.dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #f0e8e0;
  border-radius: 18px;
  box-shadow: 0 6px 12px rgba(0,0,0,0.15);
  z-index: 10;
  margin-top: 8px;
  overflow: hidden;
}
.dropdown-item {
  width: 100%;
  padding: 0.9rem;
  background: none;
  border: none;
  text-align: left;
  color: #535874;
  cursor: pointer;
  transition: background 0.2s;
}
.dropdown-item:hover {
  background: #e0d8d0;
}
</style>