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
  { to: '/dinas/rekap', label: 'Rekap' },
]

const displayName = () => (auth.isDinas ? 'Dinas Ketenagakerjaan' : auth.user?.puskesmas?.nama ?? '')

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white border-b-4 border-brand-600 shadow-sm sticky top-0 z-10">
      <div class="max-w-6xl mx-auto px-4 flex items-center justify-between h-16">
        <div class="flex items-center gap-8">
          <RouterLink :to="auth.isDinas ? '/dinas/dashboard' : '/puskesmas/dashboard'" class="flex items-center gap-2.5">
            <img src="/logo-puskesmas.png" alt="Logo Puskesmas" class="w-9 h-9 shrink-0" />
            <span class="leading-tight">
              <span class="block font-bold text-gray-900 text-base">Monitoring K3</span>
              <span class="block font-normal text-gray-500 text-xs">Puskesmas Kota Pontianak</span>
            </span>
          </RouterLink>
          <nav class="hidden md:flex gap-1">
            <RouterLink
              v-for="item in auth.isDinas ? dinasNav : puskesmasNav"
              :key="item.to"
              :to="item.to"
              class="px-3 py-2 text-[15px] rounded-md text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition"
              active-class="!text-brand-700 !bg-brand-50 font-semibold"
            >
              {{ item.label }}
            </RouterLink>
          </nav>
        </div>

        <div class="hidden md:flex items-center gap-3">
          <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-full bg-brand-600 text-white text-sm font-semibold flex items-center justify-center shrink-0">
              {{ displayName().charAt(0).toUpperCase() }}
            </span>
            <span class="text-sm text-gray-600 max-w-40 truncate">{{ displayName() }}</span>
          </div>
          <button
            class="inline-flex items-center gap-1.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md px-3 py-1.5 transition"
            @click="logout"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
            </svg>
            Keluar
          </button>
        </div>

        <button class="md:hidden text-gray-600" @click="menuOpen = !menuOpen">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>

      <div v-if="menuOpen" class="md:hidden border-t border-gray-200 px-4 py-2 space-y-1">
        <div class="flex items-center gap-2 py-2">
          <span class="w-8 h-8 rounded-full bg-brand-600 text-white text-sm font-semibold flex items-center justify-center shrink-0">
            {{ displayName().charAt(0).toUpperCase() }}
          </span>
          <span class="text-sm text-gray-600">{{ displayName() }}</span>
        </div>
        <RouterLink
          v-for="item in auth.isDinas ? dinasNav : puskesmasNav"
          :key="item.to"
          :to="item.to"
          class="block px-3 py-2.5 text-[15px] rounded-md text-gray-600 hover:bg-brand-50"
          active-class="!text-brand-700 !bg-brand-50 font-semibold"
          @click="menuOpen = false"
        >
          {{ item.label }}
        </RouterLink>
        <button
          class="w-full inline-flex items-center justify-center gap-1.5 text-[15px] font-medium text-white bg-red-600 hover:bg-red-700 rounded-md px-3 py-2.5 mt-2 transition"
          @click="logout"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
          </svg>
          Keluar
        </button>
      </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-6">
      <slot />
    </main>
  </div>
</template>
