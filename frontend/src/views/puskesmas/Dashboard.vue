<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../lib/api'
import { formatPeriode } from '../../lib/periode'
import { useAuthStore } from '../../stores/auth'

const auth = useAuthStore()
const loading = ref(true)
const periodeBulan = ref(0)
const periodeTahun = ref(0)
const kuesionerSudah = ref(false)
const kuesionerTerisi = ref(0)
const observasiSudah = ref(false)
const observasiTerisi = ref(0)

const daysLeft = computed(() => {
  const now = new Date()
  const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59, 999)
  return Math.max(0, Math.ceil((lastDay.getTime() - now.getTime()) / (1000 * 60 * 60 * 24)))
})

const belumLengkap = computed(() => {
  const list: string[] = []
  if (!kuesionerSudah.value) list.push('Kuesioner K3')
  if (!observasiSudah.value) list.push('Observasi Sarana Prasarana')
  return list
})

const isCritical = computed(() => daysLeft.value <= 3)

onMounted(async () => {
  const [q, o] = await Promise.all([
    api.get('/api/questionnaire/submissions/current'),
    api.get('/api/observation/submissions/current'),
  ])
  periodeBulan.value = q.data.periode_bulan
  periodeTahun.value = q.data.periode_tahun
  kuesionerSudah.value = q.data.submission !== null
  kuesionerTerisi.value = q.data.submission?.answers?.length ?? 0
  observasiSudah.value = o.data.submission !== null
  observasiTerisi.value = o.data.submission?.answers?.length ?? 0
  loading.value = false
})
</script>

<template>
  <div class="space-y-6">
    <div class="bg-brand-700 rounded-xl px-6 py-6">
      <p class="text-brand-100 text-sm">Selamat datang,</p>
      <h1 class="text-2xl font-bold text-white mt-0.5">{{ auth.user?.puskesmas?.nama }}</h1>
    </div>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>

    <template v-else>
      <div
        v-if="belumLengkap.length > 0"
        class="rounded-xl border-2 px-5 py-4 flex items-start gap-3"
        :class="isCritical ? 'bg-red-50 border-red-300' : 'bg-amber-50 border-amber-300'"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="w-7 h-7 shrink-0"
          :class="isCritical ? 'text-red-600' : 'text-amber-500'"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
        <div>
          <p class="font-semibold" :class="isCritical ? 'text-red-800' : 'text-amber-800'">
            Pengisian {{ belumLengkap.join(' dan ') }} belum selesai
          </p>
          <p class="text-sm mt-0.5" :class="isCritical ? 'text-red-700' : 'text-amber-700'">
            <template v-if="daysLeft > 0">
              Sisa <strong>{{ daysLeft }} hari lagi</strong> sebelum periode {{ formatPeriode(periodeBulan, periodeTahun) }} ditutup.
              Setelah itu, pengisian bulan ini tidak dapat lagi dilakukan.
            </template>
            <template v-else>
              Ini adalah <strong>hari terakhir</strong> periode {{ formatPeriode(periodeBulan, periodeTahun) }}. Segera selesaikan sebelum pukul 23:59.
            </template>
          </p>
        </div>
      </div>

      <p class="text-base text-gray-700">
        Status pengisian periode <strong>{{ formatPeriode(periodeBulan, periodeTahun) }}</strong>
      </p>

      <div class="grid sm:grid-cols-2 gap-5">
        <RouterLink
          to="/puskesmas/kuesioner"
          class="bg-white rounded-xl border-2 p-6 transition"
          :class="kuesionerSudah ? 'border-green-200 hover:border-green-400' : 'border-gray-200 hover:border-brand-400'"
        >
          <div class="flex items-start gap-4">
            <div
              class="w-14 h-14 rounded-xl flex items-center justify-center shrink-0"
              :class="kuesionerSudah ? 'bg-green-100 text-green-700' : 'bg-brand-100 text-brand-700'"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
              </svg>
            </div>
            <div class="flex-1">
              <h2 class="text-lg font-semibold text-gray-900">Kuesioner K3</h2>
              <p class="text-sm text-gray-500">Lampiran 6</p>
            </div>
            <span
              class="text-xs font-medium px-2.5 py-1 rounded-full whitespace-nowrap"
              :class="kuesionerSudah ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
            >
              {{ kuesionerSudah ? 'Sudah diisi' : 'Belum diisi' }}
            </span>
          </div>

          <div class="mt-4">
            <div class="flex items-center justify-between text-sm mb-1">
              <span class="text-gray-500">{{ kuesionerTerisi }} dari 21 pertanyaan terisi</span>
              <span class="font-medium text-gray-700">{{ Math.round((kuesionerTerisi / 21) * 100) }}%</span>
            </div>
            <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
              <div class="h-full bg-brand-600 rounded-full" :style="{ width: `${(kuesionerTerisi / 21) * 100}%` }" />
            </div>
          </div>

          <p class="mt-4 text-sm font-medium text-brand-700 flex items-center gap-1">
            {{ kuesionerSudah ? 'Lihat & Ubah Jawaban' : 'Isi Sekarang' }}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
          </p>
        </RouterLink>

        <RouterLink
          to="/puskesmas/observasi"
          class="bg-white rounded-xl border-2 p-6 transition"
          :class="observasiSudah ? 'border-green-200 hover:border-green-400' : 'border-gray-200 hover:border-brand-400'"
        >
          <div class="flex items-start gap-4">
            <div
              class="w-14 h-14 rounded-xl flex items-center justify-center shrink-0"
              :class="observasiSudah ? 'bg-green-100 text-green-700' : 'bg-brand-100 text-brand-700'"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.5 6a7.5 7.5 0 100 15 7.5 7.5 0 000-15zM10.5 6a7.5 7.5 0 000 15m9-1.5l-4.5-4.5" />
              </svg>
            </div>
            <div class="flex-1">
              <h2 class="text-lg font-semibold text-gray-900">Observasi Sarana Prasarana</h2>
              <p class="text-sm text-gray-500">Lampiran 7</p>
            </div>
            <span
              class="text-xs font-medium px-2.5 py-1 rounded-full whitespace-nowrap"
              :class="observasiSudah ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
            >
              {{ observasiSudah ? 'Sudah diisi' : 'Belum diisi' }}
            </span>
          </div>

          <div class="mt-4">
            <div class="flex items-center justify-between text-sm mb-1">
              <span class="text-gray-500">{{ observasiTerisi }} dari 96 item terisi</span>
              <span class="font-medium text-gray-700">{{ Math.round((observasiTerisi / 96) * 100) }}%</span>
            </div>
            <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
              <div class="h-full bg-brand-600 rounded-full" :style="{ width: `${(observasiTerisi / 96) * 100}%` }" />
            </div>
          </div>

          <p class="mt-4 text-sm font-medium text-brand-700 flex items-center gap-1">
            {{ observasiSudah ? 'Lihat & Ubah Jawaban' : 'Isi Sekarang' }}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
          </p>
        </RouterLink>
      </div>
    </template>
  </div>
</template>
