<template>
  <div>
    <div class="toolbar">
      <button @click="openModal" class="btn-primary">+ Создать заявку</button>
      <select v-model="statusFilter" @change="applyFilter" class="filter-select">
        <option value="">Все статусы</option>
        <option value="new">Новая</option>
        <option value="assigned">Назначена</option>
        <option value="in_progress">В работе</option>
        <option value="done">Выполнена</option>
        <option value="canceled">Отменена</option>
      </select>
    </div>

    <div class="table-wrapper">
      <table class="requests-table">
        <thead>
        <tr>
          <th>ID</th>
          <th>Клиент</th>
          <th>Телефон</th>
          <th>Адрес</th>
          <th>Описание</th>
          <th>Статус</th>
          <th>Мастер</th>
          <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="req in requests" :key="req.id">
          <td>{{ req.id }}</td>
          <td>{{ req.clientName }}</td>
          <td>{{ req.phone }}</td>
          <td>{{ req.address }}</td>
          <td>{{ req.problemText }}</td>
          <td><span class="status-badge">{{ req.status }}</span></td>
          <td>
            <select
                v-if="req.status === 'new'"
                v-model="selectedMaster[req.id]"
                @change="assign(req.id)"
                class="master-select"
            >
              <option value="">Выберите мастера</option>
              <option v-for="master in masters" :key="master.id" :value="master.id">
                {{ master.name }}
              </option>
            </select>
            <span v-else>{{ req.assigned_master?.name || '—' }}</span>
          </td>
          <td>
            <button
                v-if="req.status !== 'canceled' && req.status !== 'done'"
                @click="cancel(req.id)"
                class="btn-cancel"
            >
              Отменить
            </button>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
    <CreateRequestModal
        :show="modalOpen"
        @close="modalOpen = false"
        @created="onRequestCreated"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRequestsStore } from '@/stores/requests.js';
import apiClient from '@/api/client.js'; // для получения списка мастеров
import CreateRequestModal from '@/components/CreateRequestModal.vue';

const requestsStore = useRequestsStore();
const statusFilter = ref('');
const masters = ref([]);
const selectedMaster = ref({});
const modalOpen = ref(false);

const requests = computed(() => requestsStore.requests);

const applyFilter = () => {
  requestsStore.setFilter(statusFilter.value);
};

const assign = async (id) => {
  const masterId = selectedMaster.value[id];
  if (!masterId) return;
  try {
    await requestsStore.assignMaster(id, masterId);
    window.$toast?.('Assigned', 'success');
  } catch (error) {
    window.$toast?.(error.response?.data?.message || 'Error', 'error');
  }
};

const cancel = async (id) => {
  try {
    await requestsStore.cancelRequest(id);
    window.$toast?.('Canceled', 'success');
  } catch (error) {
    window.$toast?.(error.response?.data?.message || 'Error', 'error');
  }
};

const openModal = () => {
  modalOpen.value = true;
};

const onRequestCreated = () => {
  // Обновить список заявок после создания
  requestsStore.fetchRequests();
};

onMounted(async () => {
  await requestsStore.fetchRequests();
  // Загружаем список мастеров (можно через отдельный эндпоинт)
  const response = await apiClient.get('/users?role=master');
  masters.value = response.data;
});
</script>

<style scoped>
.toolbar {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  align-items: center;
}
.btn-primary {
  background: #c0b8b0;
  color: #535874;
  border: none;
  padding: 0.6rem 1.2rem;
  border-radius: 30px;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 500;
  box-shadow: 0 4px 6px rgba(0,0,0,0.08);
  transition: background 0.2s;
}
.btn-primary:hover {
  background: #a8a098;
}
.filter-select {
  padding: 0.6rem 1rem;
  border-radius: 30px;
  border: 1px solid #d0c8c0;
  background: white;
  font-size: 0.95rem;
  color: #535874;
  cursor: pointer;
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
  background: transparent;
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
.master-select {
  padding: 0.4rem;
  border-radius: 18px;
  border: 1px solid #d0c8c0;
  background: white;
  width: 100%;
  max-width: 160px;
}
.btn-cancel {
  background: #c0b8b0;
  color: #535874;
  border: none;
  padding: 0.4rem 1rem;
  border-radius: 30px;
  cursor: pointer;
  font-size: 0.85rem;
  transition: background 0.2s;
}
.btn-cancel:hover {
  background: #a8a098;
}
</style>