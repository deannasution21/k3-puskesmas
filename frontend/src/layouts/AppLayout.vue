<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()
const menuOpen = ref(false)

const puskesmasNav = [
  { to: '/puskesmas/dashboard', label: 'Dashboard' },
  { to: '/puskesmas/kuesioner', label: 'Kuesioner' },
  { to: '/puskesmas/observasi', label: 'Observasi' },
]

const dinasNav = [
  { to: '/dinas/dashboard', label: 'Dashboard' },
  { to: '/dinas/puskesmas', label: 'Daftar Puskesmas' },
]

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="max-w-6xl mx-auto px-4 flex items-center justify-between h-14">
        <div class="flex items-center gap-6">
          <span class="font-semibold text-gray-900 text-sm">K3 Puskesmas</span>
          <nav class="hidden md:flex gap-1">
            <RouterLink
              v-for="item in auth.isDinas ? dinasNav : puskesmasNav"
              :key="item.to"
              :to="item.to"
              class="px-3 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-100"
              active-class="!text-blue-600 !bg-blue-50 font-medium"
            >
              {{ item.label }}
            </RouterLink>
          </nav>
        </div>

        <div class="hidden md:flex items-center gap-3">
          <span class="text-sm text-gray-500">
            {{ auth.isDinas ? 'Dinas Ketenagakerjaan' : auth.user?.puskesmas?.nama }}
          </span>
          <button
            class="text-sm text-gray-500 hover:text-red-600 border border-gray-300 rounded-md px-3 py-1.5"
            @click="logout"
          >
            Keluar
          </button>
        </div>

        <button class="md:hidden text-gray-600" @click="menuOpen = !menuOpen">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>

      <div v-if="menuOpen" class="md:hidden border-t border-gray-200 px-4 py-2 space-y-1">
        <RouterLink
          v-for="item in auth.isDinas ? dinasNav : puskesmasNav"
          :key="item.to"
          :to="item.to"
          class="block px-3 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-100"
          active-class="!text-blue-600 !bg-blue-50 font-medium"
          @click="menuOpen = false"
        >
          {{ item.label }}
        </RouterLink>
        <button class="block w-full text-left px-3 py-2 text-sm text-red-600" @click="logout">Keluar</button>
      </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-6">
      <slot />
    </main>
  </div>
</template>
