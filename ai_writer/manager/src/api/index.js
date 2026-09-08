import axios from 'axios'

// Determine API base URL: works with Vite dev proxy or relative path under web server
const API_BASE = import.meta.env.VITE_API_URL || '/ai_writer/api'

const api = axios.create({
  baseURL: API_BASE,
  timeout: 45000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor: attach bearer token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('ai_writer_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Response interceptor: handle 401 unauthenticated
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('ai_writer_token')
      // Only redirect if not already on the login page
      if (!window.location.pathname.endsWith('/login')) {
        window.location.href = '/login'
      }
    }
    const message = error.response?.data?.error || error.message || 'Gözlənilməz xəta baş verdi'
    console.error('API Error:', message)
    return Promise.reject(error)
  }
)

export default api
