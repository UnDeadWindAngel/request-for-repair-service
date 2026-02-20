<template>
  <div v-if="visible" :class="['toast', type]">
    {{ message }}
  </div>
</template>

<script setup>
import { ref } from 'vue';

const visible = ref(false);
const message = ref('');
const type = ref('info');
let timeout = null;

const showToast = (msg, toastType = 'info', duration = 3000) => {
  message.value = msg;
  type.value = toastType;
  visible.value = true;
  if (timeout) clearTimeout(timeout);
  timeout = setTimeout(() => {
    visible.value = false;
  }, duration);
};

// Экспонируем метод для родительского компонента
defineExpose({ showToast });
</script>

<style scoped>
.toast {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 12px 20px;
  border-radius: 4px;
  color: white;
  z-index: 9999;
}
.info { background: #3498db; }
.success { background: #2ecc71; }
.error { background: #e74c3c; }
</style>