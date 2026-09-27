<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../lib/api'
import { formatPeriode } from '../../lib/periode'
import { extractErrorMessage } from '../../lib/errors'
import QuestionnaireForm from '../../components/QuestionnaireForm.vue'

interface Item {
  id: number
  kategori_kode: string
  kategori: string
  nomor: number
  pertanyaan: string
}

const items = ref<Item[]>([])
const answers = ref<Record<number, 'ya' | 'tidak' | undefined>>({})
const periodeBulan = ref(0)
const periodeTahun = ref(0)
const loading = ref(true)
const saving = ref(false)
const savedAt = ref<string | null>(null)
const error = ref('')

async function load() {
  loading.value = true
  const [itemsRes, currentRes] = await Promise.all([
    api.get('/api/questionnaire/items'),
    api.get('/api/questionnaire/submissions/current'),
  ])
  items.value = itemsRes.data
  periodeBulan.value = currentRes.data.periode_bulan
  periodeTahun.value = currentRes.data.periode_tahun

  const map: Record<number, 'ya' | 'tidak' | undefined> = {}
  for (const a of currentRes.data.submission?.answers ?? []) {
    map[a.item_id] = a.jawaban
  }
  answers.value = map
  loading.value = false
}

async function save() {
  error.value = ''
  const payload = Object.entries(answers.value)
    .filter(([, jawaban]) => jawaban)
    .map(([item_id, jawaban]) => ({ item_id: Number(item_id), jawaban }))

  if (payload.length === 0) {
    error.value = 'Isi minimal satu pertanyaan sebelum menyimpan.'
    return
  }

  saving.value = true
  try {
    await api.post('/api/questionnaire/submissions', { answers: payload })
    savedAt.value = new Date().toLocaleTimeString('id-ID')
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    saving.value = false
  }
}

const answeredCount = computed(() => Object.values(answers.value).filter(Boolean).length)

onMounted(load)
</script>

<template>
  <div class="space-y-5">
    <RouterLink to="/puskesmas/dashboard" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline">
      &larr; Kembali ke Beranda
    </RouterLink>

    <div class="bg-brand-700 rounded-xl px-6 py-5 flex items-center justify-between flex-wrap gap-3">
      <div>
        <h1 class="text-xl font-bold text-white">Kuesioner K3</h1>
        <p class="text-brand-100 text-sm mt-0.5">
          Lampiran 6 &middot; Periode {{ formatPeriode(periodeBulan, periodeTahun) }} &middot;
          <span class="font-medium" :class="answeredCount === items.length ? 'text-white' : 'text-amber-200'">
            {{ answeredCount }}/{{ items.length }} terjawab
          </span>
        </p>
      </div>
      <RouterLink
        to="/puskesmas/kuesioner/riwayat"
        class="inline-flex items-center gap-1.5 bg-white/10 hover:bg-white/20 text-white text-sm font-medium px-3.5 py-2 rounded-lg transition"
      >
        Lihat Riwayat
      </RouterLink>
    </div>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>

    <template v-else>
      <div class="flex items-start gap-2 bg-brand-50 border border-brand-200 rounded-lg px-4 py-2.5 text-sm text-brand-800">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
        </svg>
        <div class="space-y-0.5">
          <p>Cara isi: pilih <strong class="text-green-700">Ya</strong> atau <strong class="text-red-600">Tidak</strong> pada tiap pertanyaan, lalu tekan <strong>Simpan</strong>.</p>
          <p>Bisa disimpan bertahap — lanjutkan kapan saja selama masih bulan berjalan.</p>
        </div>
      </div>

      <QuestionnaireForm :items="items" v-model="answers" />

      <div class="sticky bottom-0 bg-gray-50/95 backdrop-blur border-t border-gray-200 py-3 flex items-center gap-3">
        <button
          class="bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white text-base font-medium px-5 py-2.5 rounded-lg transition"
          :disabled="saving"
          @click="save"
        >
          {{ saving ? 'Menyimpan...' : 'Simpan' }}
        </button>
        <span v-if="savedAt" class="text-sm text-green-600">Tersimpan pukul {{ savedAt }}</span>
        <span v-if="error" class="text-sm text-red-600">{{ error }}</span>
      </div>
    </template>
  </div>
</template>
