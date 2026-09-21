<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import api from '../../lib/api'
import { extractErrorMessage, extractFieldErrors } from '../../lib/errors'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)
const loading = ref(isEdit.value)
const saving = ref(false)
const error = ref('')
const fieldErrors = ref<Record<string, string>>({})

const nama = ref('')
const alamat = ref('')
const username = ref('')
const password = ref('')

onMounted(async () => {
  if (!isEdit.value) return
  const { data } = await api.get(`/api/dashboard/puskesmas/${route.params.id}`)
  nama.value = data.puskesmas.nama
  alamat.value = data.puskesmas.alamat ?? ''
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
        username: username.value,
      })
      router.push(`/dinas/puskesmas/${route.params.id}`)
    } else {
      const { data } = await api.post('/api/admin/puskesmas', {
        nama: nama.value,
        alamat: alamat.value || null,
        username: username.value,
        password: password.value,
      })
      router.push(`/dinas/puskesmas/${data.id}`)
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
    <h1 class="text-xl font-semibold text-gray-900">
      {{ isEdit ? 'Edit Puskesmas' : 'Tambah Puskesmas' }}
    </h1>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>

    <form v-else class="bg-white rounded-lg border border-gray-200 p-5 space-y-4" @submit.prevent="submit">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Puskesmas</label>
        <input
          v-model="nama"
          type="text"
          required
          class="w-full text-sm border rounded-md px-3 py-2"
          :class="fieldErrors.nama ? 'border-red-400' : 'border-gray-300'"
        />
        <p v-if="fieldErrors.nama" class="text-xs text-red-600 mt-1">{{ fieldErrors.nama }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
        <textarea v-model="alamat" rows="2" class="w-full text-sm border border-gray-300 rounded-md px-3 py-2"></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Username Login</label>
        <input
          v-model="username"
          type="text"
          required
          class="w-full text-sm border rounded-md px-3 py-2"
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
          class="w-full text-sm border rounded-md px-3 py-2"
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
          class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-medium px-4 py-2 rounded-md"
        >
          {{ saving ? 'Menyimpan...' : 'Simpan' }}
        </button>
        <RouterLink
          :to="isEdit ? `/dinas/puskesmas/${route.params.id}` : '/dinas/puskesmas'"
          class="text-sm text-gray-500 px-4 py-2"
        >
          Batal
        </RouterLink>
      </div>
    </form>
  </div>
</template>
