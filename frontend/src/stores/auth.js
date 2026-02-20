import { defineStore } from 'pinia';
import { login as apiLogin, logout as apiLogout, me } from '@/api/auth';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
    }),
    getters: {
        isAuthenticated: (state) => !!state.user,
        role: (state) => state.user?.role,
    },
    actions: {
        async login(credentials) {
            try {
                const response = await apiLogin(credentials);
                this.user = response.data.user;
                return response;
            } catch (error) {
                throw error;
            }
        },
        async logout() {
            await apiLogout();
            this.user = null;
        },
        async fetchUser() {
            try {
                const response = await me();
                this.user = response.data;
            } catch {
                this.user = null;
            }
        },
    },
});