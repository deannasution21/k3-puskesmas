<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { extractErrorMessage } from '../lib/errors'

const username = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

const auth = useAuthStore()
const router = useRouter()

async function onSubmit() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(username.value, password.value)
    router.push(auth.isDinas ? '/dinas/dashboard' : '/puskesmas/dashboard')
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <form class="w-full max-w-sm bg-white shadow rounded-lg p-8 space-y-4" @submit.prevent="onSubmit">
      <div>
        <h1 class="text-lg font-semibold text-gray-900">Sistem Monitoring K3 Puskesmas</h1>
        <p class="text-sm text-gray-500">Kota Pontianak</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input
          v-model="username"
          type="text"
          required
          class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input
          v-model="password"
          type="password"
          required
          class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

      <button
        type="submit"
        :disabled="loading"
        class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-medium py-2 rounded-md transition"
      >
        {{ loading ? 'Memproses...' : 'Login' }}
      </button>
    </form>
  </div>
</template>
