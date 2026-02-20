import apiClient from './client';

export const getRequests = (params) => apiClient.get('/requests', { params });
export const createRequest = (data) => apiClient.post('/requests', data);
export const assignMaster = (id, masterId) => apiClient.post(`/requests/${id}/assign`, { master_id: masterId });
export const cancelRequest = (id) => apiClient.post(`/requests/${id}/cancel`);
export const takeRequest = (id) => apiClient.post(`/requests/${id}/take`);
export const completeRequest = (id) => apiClient.post(`/requests/${id}/complete`);