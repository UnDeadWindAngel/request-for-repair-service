import { defineStore } from 'pinia';
import * as api from '@/api/requests';

export const useRequestsStore = defineStore('requests', {
    state: () => ({
        requests: [],
        filters: { status: '' },
    }),
    actions: {
        async fetchRequests() {
            try {
                const response = await api.getRequests(this.filters);
                this.requests = response.data;
            } catch (error) {
                console.error('Failed to fetch requests:', error);
            }
        },
        async createRequest(data) {
            const response = await api.createRequest(data);
            this.requests.push(response.data);
            return response;
        },
        async assignMaster(id, masterId) {
            const response = await api.assignMaster(id, masterId);
            const index = this.requests.findIndex(r => r.id === id);
            if (index !== -1) this.requests[index] = response.data;
        },
        async cancelRequest(id) {
            const response = await api.cancelRequest(id);
            const index = this.requests.findIndex(r => r.id === id);
            if (index !== -1) this.requests[index] = response.data;
        },
        async takeRequest(id) {
            const response = await api.takeRequest(id);
            const index = this.requests.findIndex(r => r.id === id);
            if (index !== -1) this.requests[index] = response.data;
        },
        async completeRequest(id) {
            const response = await api.completeRequest(id);
            const index = this.requests.findIndex(r => r.id === id);
            if (index !== -1) this.requests[index] = response.data;
        },
        setFilter(status) {
            this.filters.status = status;
            this.fetchRequests();
        },
    },
});