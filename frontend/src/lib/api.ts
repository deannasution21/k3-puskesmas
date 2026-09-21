import axios from 'axios'

const api = axios.create({
  baseURL: '/',
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: 'application/json',
  },
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const url: string = error?.config?.url ?? ''
    const isAuthCheck = url.endsWith('/api/me') || url.endsWith('/api/login')

    if (error?.response?.status === 401 && !isAuthCheck) {
      window.location.href = '/login'
    }

    return Promise.reject(error)
  },
)

export default api
