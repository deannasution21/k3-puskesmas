<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import api from '../../lib/api'
import { extractErrorMessage, extractFieldErrors } from '../../lib/errors'
import CredentialBox from '../../components/CredentialBox.vue'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)
const loading = ref(isEdit.value)
const saving = ref(false)
const error = ref('')
const fieldErrors = ref<Record<string, string>>({})

const nama = ref('')
const alamat = ref('')
const kepalaPuskesmas = ref('')
const noHp = ref('')
const email = ref('')
const kodePuskesmas = ref('')
const username = ref('')
const password = ref('')

const created = ref<{ id: number; username: string; password: string } | null>(null)

onMounted(async () => {
  if (!isEdit.value) return
  const { data } = await api.get(`/api/dashboard/puskesmas/${route.params.id}`)
  nama.value = data.puskesmas.nama
  alamat.value = data.puskesmas.alamat ?? ''
  kepalaPuskesmas.value = data.puskesmas.kepala_puskesmas ?? ''
  noHp.value = data.puskesmas.no_hp ?? ''
  email.value = data.puskesmas.email ?? ''
  kodePuskesmas.value = data.puskesmas.kode_puskesmas ?? ''
  username.value = data.puskesmas.user?.username ?? ''
  loading.value = false
})

async function submit() {
  error.value = ''
  fieldErrors.value = {}
  saving.value = true
  try {
    if (isEdit.value) {
      await api.put(`/api/admin/puskesmas/${route.params.id}`, {
        nama: nama.value,
        alamat: alamat.value || null,
        kepala_puskesmas: kepalaPuskesmas.value || null,
        no_hp: noHp.value || null,
        email: email.value || null,
        kode_puskesmas: kodePuskesmas.value || null,
        username: username.value,
      })
      router.push(`/dinas/puskesmas/${route.params.id}`)
    } else {
      const { data } = await api.post('/api/admin/puskesmas', {
        nama: nama.value,
        alamat: alamat.value || null,
        kepala_puskesmas: kepalaPuskesmas.value || null,
        no_hp: noHp.value || null,
        email: email.value || null,
        kode_puskesmas: kodePuskesmas.value || null,
        username: username.value,
        password: password.value,
      })
      created.value = { id: data.id, username: username.value, password: password.value }
    }
  } catch (e) {
    fieldErrors.value = extractFieldErrors(e)
    error.value = extractErrorMessage(e)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="max-w-lg space-y-4">
    <h1 class="text-xl font-bold text-gray-900">
      {{ isEdit ? 'Ubah Data Puskesmas' : 'Tambah Puskesmas' }}
    </h1>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>

    <div v-else-if="created" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
      <p class="text-sm text-gray-600">
        Puskesmas berhasil dibuat. Sampaikan kredensial berikut ke petugas puskesmas:
      </p>
      <CredentialBox :username="created.username" :password="created.password" />
      <button
        class="w-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition"
        @click="router.push(`/dinas/puskesmas/${created.id}`)"
      >
        Lanjut ke Detail Puskesmas
      </button>
    </div>

    <form v-else class="bg-white rounded-xl border border-gray-200 p-6 space-y-4" @submit.prevent="submit">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Puskesmas</label>
        <input
          v-model="nama"
          type="text"
          required
          class="w-full text-sm border rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500"
          :class="fieldErrors.nama ? 'border-red-400' : 'border-gray-300'"
        />
        <p v-if="fieldErrors.nama" class="text-xs text-red-600 mt-1">{{ fieldErrors.nama }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
        <textarea v-model="alamat" rows="2" class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kepala Puskesmas</label>
        <input v-model="kepalaPuskesmas" type="text" class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500" />
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
          <input v-model="noHp" type="text" class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Kode Puskesmas</label>
          <input
            v-model="kodePuskesmas"
            type="text"
            class="w-full text-sm border rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500"
            :class="fieldErrors.kode_puskesmas ? 'border-red-400' : 'border-gray-300'"
          />
          <p v-if="fieldErrors.kode_puskesmas" class="text-xs text-red-600 mt-1">{{ fieldErrors.kode_puskesmas }}</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input
          v-model="email"
          type="email"
          class="w-full text-sm border rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500"
          :class="fieldErrors.email ? 'border-red-400' : 'border-gray-300'"
        />
        <p v-if="fieldErrors.email" class="text-xs text-red-600 mt-1">{{ fieldErrors.email }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Username Login</label>
        <input
          v-model="username"
          type="text"
          required
          class="w-full text-sm border rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500"
          :class="fieldErrors.username ? 'border-red-400' : 'border-gray-300'"
        />
        <p v-if="fieldErrors.username" class="text-xs text-red-600 mt-1">{{ fieldErrors.username }}</p>
      </div>

      <div v-if="!isEdit">
        <label class="block text-sm font-medium text-gray-700 mb-1">Password Awal</label>
        <input
          v-model="password"
          type="text"
          required
          minlength="8"
          class="w-full text-sm border rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500"
          :class="fieldErrors.password ? 'border-red-400' : 'border-gray-300'"
        />
        <p v-if="fieldErrors.password" class="text-xs text-red-600 mt-1">{{ fieldErrors.password }}</p>
        <p v-else class="text-xs text-gray-400 mt-1">Minimal 8 karakter. Sampaikan ke petugas puskesmas secara langsung.</p>
      </div>

      <p v-if="error && Object.keys(fieldErrors).length === 0" class="text-sm text-red-600">{{ error }}</p>

      <div class="flex gap-2">
        <button
          type="submit"
          :disabled="saving"
          class="bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition"
        >
          {{ saving ? 'Menyimpan...' : 'Simpan' }}
        </button>
        <RouterLink
          :to="isEdit ? `/dinas/puskesmas/${route.params.id}` : '/dinas/puskesmas'"
          class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5"
        >
          Batal
        </RouterLink>
      </div>
    </form>
  </div>
</template>
