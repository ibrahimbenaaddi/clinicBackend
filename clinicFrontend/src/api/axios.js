import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('clinic_token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

api.interceptors.response.use(
  (res) => {
    // Laravel Resource collections often wrap data in a nested 'data' key.
    // If res.data.data.data exists and is an array, we unwrap it to simplify frontend usage.
    if (res.data?.data && res.data.data.data && Array.isArray(res.data.data.data)) {
      res.data.data = res.data.data.data;
    }
    return res;
  },
  (err) => {
    if (err.response?.status === 401 && !window.location.pathname.includes('/login')) {
      localStorage.removeItem('clinic_token');
      localStorage.removeItem('clinic_user');
      window.location.href = '/login';
    }
    return Promise.reject(err);
  }
);

export default api;
