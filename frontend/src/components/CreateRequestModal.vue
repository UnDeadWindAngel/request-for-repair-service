<template>
  <Teleport to="body">
    <div v-if="show" class="modal-overlay" @click.self="close">
      <div class="modal-card">
        <h2>Новая заявка</h2>
        <form @submit.prevent="submit">
          <div class="form-group">
            <label for="clientName">Имя клиента</label>
            <input id="clientName" v-model="form.clientName" required />
          </div>
          <div class="form-group">
            <label for="phone">Телефон</label>
            <input id="phone" v-model="form.phone" required />
          </div>
          <div class="form-group">
            <label for="address">Адрес</label>
            <input id="address" v-model="form.address" required />
          </div>
          <div class="form-group">
            <label for="problemText">Описание проблемы</label>
            <textarea id="problemText" v-model="form.problemText" required rows="3"></textarea>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn-secondary" @click="close">Отмена</button>
            <button type="submit" class="btn-primary">Создать</button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { reactive, defineProps, defineEmits } from 'vue';
import { useRequestsStore } from '@/stores/requests';

const props = defineProps({
  show: Boolean,
});
const emit = defineEmits(['close', 'created']);

const requestsStore = useRequestsStore();
const form = reactive({
  clientName: '',
  phone: '',
  address: '',
  problemText: '',
});

const submit = async () => {
  try {
    await requestsStore.createRequest(form);
    // Очистить форму
    form.clientName = '';
    form.phone = '';
    form.address = '';
    form.problemText = '';
    emit('created');
    close();
  } catch (error) {
    window.$toast?.(error.response?.data?.message || 'Ошибка создания', 'error');
  }
};

const close = () => {
  emit('close');
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}
.modal-card {
  background: #e0d8d0;
  padding: 2rem;
  border-radius: 24px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}
h2 {
  color: #535874;
  margin-bottom: 1.5rem;
  font-weight: 500;
  text-align: center;
}
.form-group {
  margin-bottom: 1.2rem;
}
label {
  display: block;
  margin-bottom: 0.3rem;
  color: #535874;
  font-weight: 500;
  font-size: 0.9rem;
}
input, textarea {
  width: 100%;
  padding: 0.8rem 1rem;
  border: 1px solid #d0c8c0;
  border-radius: 18px;
  background: white;
  font-size: 1rem;
  box-sizing: border-box;
}
input:focus, textarea:focus {
  outline: none;
  border-color: #535874;
}
.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}
.btn-primary, .btn-secondary {
  padding: 0.6rem 1.5rem;
  border: none;
  border-radius: 30px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
  box-shadow: 0 4px 6px rgba(0,0,0,0.08);
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
</style>