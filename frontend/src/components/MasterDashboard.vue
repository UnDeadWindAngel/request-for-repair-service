<template>
  <div>
    <h2 class="section-title">Мои заявки</h2>
    <div class="table-wrapper">
      <table class="requests-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Клиент</th>
            <th>Телефон</th>
            <th>Адрес</th>
            <th>Статус</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="req in myRequests" :key="req.id">
            <td>{{ req.id }}</td>
            <td>{{ req.clientName }}</td>
            <td>{{ req.phone }}</td>
            <td>{{ req.address }}</td>
            <td><span class="status-badge">{{ req.status }}</span></td>
            <td>
              <!-- Кнопка "Взять в работу" только для статуса assigned -->
              <button
                  v-if="req.status === 'assigned'"
                  @click="take(req.id)"
                  class="btn-action"
              >
                Взять в работу
              </button>
              <!-- Кнопка "Завершить" только для статуса in_progress -->
              <button
                  v-else-if="req.status === 'in_progress'"
                  @click="complete(req.id)"
                  class="btn-action"
              >
                Завершить
              </button>
              <!-- Если другие статусы – нет действий -->
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRequestsStore } from '@/stores/requests.js';
import { useAuthStore } from '@/stores/auth.js';

const requestsStore = useRequestsStore();
const authStore = useAuthStore();

const myRequests = computed(() =>
    requestsStore.requests.filter(req => req.assignedTo === authStore.user.id)
);

const take = async (id) => {
  try {
    await requestsStore.takeRequest(id);
    window.$toast?.('Заявка взята в работу', 'success');
  } catch (error) {
    window.$toast?.(error.response?.data?.message || 'Ошибка', 'error');
  }
};

const complete = async (id) => {
  try {
    await requestsStore.completeRequest(id);
    window.$toast?.('Заявка выполнена', 'success');
  } catch (error) {
    window.$toast?.(error.response?.data?.message || 'Ошибка', 'error');
  }
};

onMounted(() => {
  requestsStore.fetchRequests();
});
</script>

<style scoped>
.section-title {
  color: #535874;
  font-weight: 500;
  margin-bottom: 1.5rem;
}
.table-wrapper {
  background: #e0d8d0;
  border-radius: 24px;
  padding: 1.5rem;
  box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
.requests-table {
  width: 100%;
  border-collapse: collapse;
}
.requests-table th,
.requests-table td {
  padding: 1rem 0.5rem;
  text-align: left;
  border-bottom: 1px solid #d0c8c0;
  color: #535874;
}
.requests-table th {
  font-weight: 600;
}
.requests-table tr:last-child td {
  border-bottom: none;
}
.status-badge {
  background: #f0e8e0;
  padding: 0.3rem 0.8rem;
  border-radius: 30px;
  font-size: 0.85rem;
  display: inline-block;
}
.status-select {
  padding: 0.4rem;
  border-radius: 18px;
  border: 1px solid #d0c8c0;
  background: white;
  width: 100%;
  max-width: 140px;
  color: #535874;
}
.btn-action {
  background: #c0b8b0;
  color: #535874;
  border: none;
  padding: 0.6rem 1.2rem;  /* сделаем чуть больше, как у диспетчера */
  border-radius: 30px;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 500;
  box-shadow: 0 4px 6px rgba(0,0,0,0.08);
  transition: background 0.2s;
}
.btn-action:hover {
  background: #a8a098;
}
</style>