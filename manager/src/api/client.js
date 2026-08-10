import axios from 'axios'

// Detect base URL from hostname or configuration
const isDev = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
const API_BASE_URL = isDev ? 'http://localhost:8000/api' : 'https://alt404.com/api'

const client = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json'
  }
})

// Inject bearer token from localStorage
client.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
}, (error) => {
  return Promise.reject(error)
})

// Auto-handle 401 Unauthorized
client.interceptors.response.use((response) => {
  return response
}, (error) => {
  if (error.response && error.response.status === 401) {
    localStorage.removeItem('auth_token')
    window.location.href = '/login'
  }
  return Promise.reject(error)
})

export default client
export { API_BASE_URL }
