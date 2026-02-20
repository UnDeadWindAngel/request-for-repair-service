import axios from 'axios';
import apiClient from './client';

export const login = async (credentials) => {
    await axios.get('/sanctum/csrf-cookie');
    return apiClient.post('/login', credentials);
}
export const logout = () => apiClient.post('/logout');
export const me = () => apiClient.get('/me');