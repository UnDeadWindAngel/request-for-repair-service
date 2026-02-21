import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import Login from '../views/Login.vue';
import { createPinia, setActivePinia } from 'pinia';
import { useAuthStore } from '../stores/auth';

// Мокаем router
vi.mock('vue-router', () => ({
    useRouter: () => ({
        push: vi.fn(),
    }),
}));

// Мокаем apiClient
vi.mock('../api/client', () => ({
    default: {
        post: vi.fn(),
    },
}));

describe('Login.vue', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('renders login form', () => {
        const wrapper = mount(Login);
        expect(wrapper.find('input[type="email"]').exists()).toBe(true);
        expect(wrapper.find('input[type="password"]').exists()).toBe(true);
        expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
    });

    it('calls login action on submit', async () => {
        const wrapper = mount(Login);
        const authStore = useAuthStore();
        const loginSpy = vi.spyOn(authStore, 'login').mockResolvedValue({});

        await wrapper.find('input[type="email"]').setValue('test@example.com');
        await wrapper.find('input[type="password"]').setValue('password');
        await wrapper.find('form').trigger('submit.prevent');

        expect(loginSpy).toHaveBeenCalledWith({ email: 'test@example.com', password: 'password' });
    });
});