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

// Response error handler
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const message = error.response?.data?.error || error.message || 'Gözlənilməz xəta baş verdi'
    console.error('API Error:', message)
    return Promise.reject(error)
  }
)

export default api
