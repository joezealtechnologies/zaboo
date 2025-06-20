import axios from 'axios';

// Use environment variable or fallback to production URL
const API_BASE_URL = import.meta.env.VITE_API_URL || 
  (import.meta.env.PROD ? 'https://fazona.org/api' : 'http://localhost:5000/api');

console.log('🔍 API Base URL:', API_BASE_URL);
console.log('🔍 Environment:', import.meta.env.PROD ? 'Production' : 'Development');

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000, // 10 second timeout
});

// Add auth token to requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('admin_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  console.log('🚀 Making request to:', config.url);
  return config;
});

// Add response interceptor for debugging
api.interceptors.response.use(
  (response) => {
    console.log('✅ API Response:', response.status, response.config.url);
    return response;
  },
  (error) => {
    console.error('❌ API Error:', error.response?.status, error.config?.url, error.message);
    if (error.response?.status === 404) {
      console.error('🔍 API endpoint not found. Check if backend is running.');
    }
    if (error.code === 'ECONNABORTED') {
      console.error('⏰ Request timeout. Backend might be slow or not responding.');
    }
    return Promise.reject(error);
  }
);

export interface Vehicle {
  id: number;
  name: string;
  price: string;
  range_km: string;
  description?: string;
  features: string[];
  badge?: string;
  badge_color?: string;
  rating: number;
  is_active: boolean;
  images: string[];
  image?: string;
  primary_image?: string;
  created_at: string;
  updated_at: string;
}

export interface AdminUser {
  id: number;
  username: string;
  email: string;
}

export interface LoginResponse {
  token: string;
  user: AdminUser;
}

// Public API
export const vehicleAPI = {
  getAll: () => {
    console.log('🚗 Fetching vehicles from:', `${API_BASE_URL}/vehicles`);
    return api.get<Vehicle[]>('/vehicles');
  },
};

// Admin API
export const adminAPI = {
  login: (credentials: { username: string; password: string }) => {
    console.log('🔐 Attempting admin login to:', `${API_BASE_URL}/admin/login`);
    return api.post<LoginResponse>('/admin/login', credentials);
  },
  
  vehicles: {
    getAll: () => api.get<Vehicle[]>('/admin/vehicles'),
    create: (formData: FormData) => api.post('/admin/vehicles', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }),
    update: (id: number, formData: FormData) => api.put(`/admin/vehicles/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }),
    delete: (id: number) => api.delete(`/admin/vehicles/${id}`),
    deleteImage: (vehicleId: number, imageId: number) => 
      api.delete(`/admin/vehicles/${vehicleId}/images/${imageId}`),
    setPrimaryImage: (vehicleId: number, imageId: number) =>
      api.put(`/admin/vehicles/${vehicleId}/images/${imageId}/primary`),
  },
};

export default api;