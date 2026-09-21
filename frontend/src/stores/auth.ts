import { defineStore } from 'pinia'
import api from '../lib/api'

interface User {
  id: number
  username: string
  role: 'dinas' | 'puskesmas'
  puskesmas: { id: number; nama: string } | null
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
    initialized: false,
  }),
  getters: {
    isAuthenticated: (state) => state.user !== null,
    isDinas: (state) => state.user?.role === 'dinas',
    isPuskesmas: (state) => state.user?.role === 'puskesmas',
  },
  actions: {
    async login(username: string, password: string) {
      await api.get('/sanctum/csrf-cookie')
      await api.post('/api/login', { username, password })
      await this.fetchUser()
    },
    async logout() {
      await api.post('/api/logout')
      this.user = null
    },
    async fetchUser() {
      try {
        const { data } = await api.get('/api/me')
        this.user = data
      } catch {
        this.user = null
      } finally {
        this.initialized = true
      }
    },
  },
})
