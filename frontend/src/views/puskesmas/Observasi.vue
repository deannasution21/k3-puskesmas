<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../lib/api'
import { formatPeriode } from '../../lib/periode'
import { extractErrorMessage } from '../../lib/errors'
import ObservationForm, { type ObservationAnswer } from '../../components/ObservationForm.vue'

interface Item {
  id: number
  kategori_kode: string
  kategori: string
  nomor: number
  item_teks: string
  skala_kondisi: 'baik_buruk' | 'baik_rusak_ringan_rusak_berat'
}

const items = ref<Item[]>([])
const answers = ref<Record<number, ObservationAnswer>>({})
const tanggalObservasi = ref('')
const periodeBulan = ref(0)
const periodeTahun = ref(0)
const loading = ref(true)
const saving = ref(false)
const savedAt = ref<string | null>(null)
const error = ref('')

async function load() {
  loading.value = true
  const [itemsRes, currentRes] = await Promise.all([
    api.get('/api/observation/items'),
    api.get('/api/observation/submissions/current'),
  ])
  items.value = itemsRes.data
  periodeBulan.value = currentRes.data.periode_bulan
  periodeTahun.value = currentRes.data.periode_tahun

  const submission = currentRes.data.submission
  tanggalObservasi.value = submission?.tanggal_observasi?.slice(0, 10) ?? ''

  const map: Record<number, ObservationAnswer> = {}
  for (const a of submission?.answers ?? []) {
    map[a.item_id] = { ada: a.ada, kondisi: a.kondisi, keterangan: a.keterangan }
  }
  answers.value = map
  loading.value = false
}

async function save() {
  error.value = ''
  const payload = Object.entries(answers.value)
    .filter(([, a]) => a.ada)
    .map(([item_id, a]) => ({
      item_id: Number(item_id),
      ada: a.ada,
      kondisi: a.ada === 'ada' ? a.kondisi ?? null : null,
      keterangan: a.keterangan || null,
    }))

  if (payload.length === 0) {
    error.value = 'Isi minimal satu item sebelum menyimpan.'
    return
  }

  const belumAdaKondisi = payload.filter((a) => a.ada === 'ada' && !a.kondisi).length
  if (belumAdaKondisi > 0) {
    error.value = `${belumAdaKondisi} item berstatus "Ada" belum diisi kondisinya (Baik/Buruk/Rusak).`
    return
  }

  saving.value = true
  try {
    await api.post('/api/observation/submissions', {
      tanggal_observasi: tanggalObservasi.value || null,
      answers: payload,
    })
    savedAt.value = new Date().toLocaleTimeString('id-ID')
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    saving.value = false
  }
}

const answeredCount = computed(() => Object.values(answers.value).filter((a) => a.ada).length)

onMounted(load)
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-2">
      <div>
        <h1 class="text-xl font-semibold text-gray-900">Observasi Sarana Prasarana K3 (Lampiran 7)</h1>
        <p class="text-sm text-gray-500">
          Periode {{ formatPeriode(periodeBulan, periodeTahun) }} &middot;
          <span :class="answeredCount === items.length ? 'text-green-600' : 'text-yellow-600'">
            {{ answeredCount }}/{{ items.length }} terjawab
          </span>
        </p>
      </div>
      <RouterLink to="/puskesmas/observasi/riwayat" class="text-sm text-blue-600 hover:underline">
        Lihat riwayat
      </RouterLink>
    </div>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>

    <template v-else>
      <div class="bg-white rounded-lg border border-gray-200 p-4 flex items-center gap-3">
        <label class="text-sm text-gray-700">Tanggal Observasi</label>
        <input
          type="date"
          v-model="tanggalObservasi"
          class="text-sm border border-gray-300 rounded-md px-2 py-1"
        />
      </div>

      <ObservationForm :items="items" v-model="answers" />

      <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 py-3 flex items-center gap-3">
        <button
          class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-medium px-4 py-2 rounded-md"
          :disabled="saving"
          @click="save"
        >
          {{ saving ? 'Menyimpan...' : 'Simpan' }}
        </button>
        <span v-if="savedAt" class="text-xs text-green-600">Tersimpan pukul {{ savedAt }}</span>
        <span v-if="error" class="text-xs text-red-600">{{ error }}</span>
      </div>
    </template>
  </div>
</template>
