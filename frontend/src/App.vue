<template>
  <router-view />
  <Toast ref="toastRef" />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Toast from '@/components/Toast.vue'

const toastRef = ref(null);

onMounted(() => {
  // Делаем функцию глобально доступной
  window.$toast = (msg, type, duration) => {
    toastRef.value?.showToast(msg, type, duration);
  };
});
</script>

<style scoped></style>