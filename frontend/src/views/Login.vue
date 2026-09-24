<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { extractErrorMessage } from '../lib/errors'

const username = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const showPassword = ref(false)

const auth = useAuthStore()
const router = useRouter()

const lupaPasswordLink = computed(() => {
  const pesan = username.value.trim()
    ? `Halo Admin, saya lupa password akun Monitoring K3 Puskesmas dengan username "${username.value.trim()}". Mohon bantuan untuk reset password. Terima kasih.`
    : 'Halo Admin, saya lupa password akun Monitoring K3 Puskesmas. Mohon bantuan untuk reset password. Terima kasih.'
  return `https://wa.me/628994197378?text=${encodeURIComponent(pesan)}`
})

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
  <div class="min-h-screen flex items-center justify-center bg-brand-50 px-4 py-10">
    <div class="w-full max-w-sm">
      <div class="flex flex-col items-center text-center mb-6">
        <img src="/logo-puskesmas.png" alt="Logo Puskesmas" class="w-20 h-20 mb-3" />
        <h1 class="text-xl font-bold text-gray-900">Monitoring K3 Puskesmas</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kota Pontianak</p>
      </div>

      <form class="bg-white shadow-sm rounded-xl border border-gray-200 p-7 space-y-5" @submit.prevent="onSubmit">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
          <input
            v-model="username"
            type="text"
            required
            autofocus
            placeholder="Masukkan username"
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
          <div class="relative">
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              required
              placeholder="Masukkan password"
              class="w-full rounded-lg border border-gray-300 pl-3.5 pr-11 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            />
            <button
              type="button"
              class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600"
              tabindex="-1"
              @click="showPassword = !showPassword"
            >
              <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </button>
          </div>
        </div>

        <p v-if="error" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">{{ error }}</p>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white text-base font-medium py-2.5 rounded-lg transition"
        >
          {{ loading ? 'Memproses...' : 'Masuk' }}
        </button>

        <a
          :href="lupaPasswordLink"
          target="_blank"
          rel="noopener noreferrer"
          class="flex items-center justify-center gap-1.5 text-sm font-medium text-brand-700 hover:text-brand-800 hover:underline"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.626.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
            <path d="M12.004 2.003c-5.514 0-9.997 4.483-9.997 9.997 0 1.763.464 3.484 1.345 4.997L2 22l5.135-1.335a9.96 9.96 0 004.869 1.24h.004c5.514 0 9.997-4.483 9.997-9.997s-4.483-9.905-9.997-9.905zm5.848 15.845a8.29 8.29 0 01-5.848 2.421h-.003a8.293 8.293 0 01-4.226-1.157l-.303-.18-3.05.793.815-2.973-.198-.305a8.263 8.263 0 01-1.268-4.404c0-4.582 3.73-8.312 8.315-8.312 2.222 0 4.31.866 5.88 2.438a8.262 8.262 0 012.43 5.882c0 4.583-3.73 8.312-8.544 7.797z"/>
          </svg>
          Lupa password? Hubungi admin via WhatsApp
        </a>
      </form>

      <p class="text-center text-xs text-gray-400 mt-6">
        Dinas Ketenagakerjaan &amp; Puskesmas Kota Pontianak
      </p>
    </div>
  </div>
</template>
