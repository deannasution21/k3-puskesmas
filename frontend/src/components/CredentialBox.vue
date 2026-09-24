<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
  username: string
  password?: string | null
}>()

const copied = ref(false)

async function copy() {
  const text = props.password
    ? `Username: ${props.username}\nPassword: ${props.password}`
    : props.username

  try {
    await navigator.clipboard.writeText(text)
    copied.value = true
    setTimeout(() => (copied.value = false), 2000)
  } catch {
    // Clipboard API tidak tersedia (mis. konteks non-HTTPS) — abaikan diam-diam.
  }
}
</script>

<template>
  <div class="bg-brand-50 border border-brand-200 rounded-lg p-4 space-y-3">
    <div>
      <p class="text-xs text-brand-700 font-medium mb-1">Username</p>
      <p class="font-mono text-sm text-brand-900 bg-white border border-brand-200 rounded-md px-3 py-2 select-all">
        {{ username }}
      </p>
    </div>

    <div v-if="password">
      <p class="text-xs text-brand-700 font-medium mb-1">Password</p>
      <p class="font-mono text-sm text-brand-900 bg-white border border-brand-200 rounded-md px-3 py-2 select-all">
        {{ password }}
      </p>
    </div>
    <p v-else class="text-xs text-gray-500">
      Password tersembunyi demi keamanan. Gunakan tombol "Ubah Password" untuk membuat password baru.
    </p>

    <button
      type="button"
      class="w-full text-sm font-medium rounded-lg px-3 py-2.5 transition"
      :class="copied ? 'bg-green-600 text-white' : 'bg-brand-600 hover:bg-brand-700 text-white'"
      @click="copy"
    >
      {{ copied ? 'Tersalin!' : password ? 'Salin Username & Password' : 'Salin Username' }}
    </button>
  </div>
</template>
