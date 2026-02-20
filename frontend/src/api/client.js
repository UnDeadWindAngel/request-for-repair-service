import axios from 'axios';

axios.defaults.withCredentials = true;

const apiClient = axios.create({
    baseURL: '/api',  // через прокси nginx
    headers: {
        'Content-Type': 'application/json',
    },
});

// Перехватчик для обработки ошибок
apiClient.interceptors.response.use(
    (response) => response,
    (error) => {
        // Можно показывать уведомления через глобальный метод
        return Promise.reject(error);
    }
);

export default apiClient;