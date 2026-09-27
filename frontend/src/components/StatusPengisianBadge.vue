<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  sudahIsi: boolean
  periodeBulan: number
  periodeTahun: number
}>()

const status = computed(() => {
  if (props.sudahIsi) {
    return { label: 'Sudah Mengisi', classes: 'bg-green-100 text-green-700', icon: 'check' }
  }

  const now = new Date()
  const isPast =
    props.periodeTahun < now.getFullYear() ||
    (props.periodeTahun === now.getFullYear() && props.periodeBulan < now.getMonth() + 1)

  if (isPast) {
    return { label: 'Tidak Mengisi', classes: 'bg-red-100 text-red-600', icon: 'x-red' }
  }

  return { label: 'Belum Mengisi', classes: 'bg-gray-100 text-gray-500', icon: 'x-gray' }
})
</script>

<template>
  <span
    class="inline-flex items-center gap-0.5 sm:gap-1.5 text-xs sm:text-sm font-medium px-1.5 py-1 sm:px-3.5 sm:py-2 rounded-full whitespace-nowrap"
    :class="status.classes"
  >
    <svg
      v-if="status.icon === 'check'"
      xmlns="http://www.w3.org/2000/svg"
      class="w-3.5 h-3.5 sm:w-5 sm:h-5 shrink-0"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5" />
    </svg>
    <svg
      v-else
      xmlns="http://www.w3.org/2000/svg"
      class="w-3.5 h-3.5 sm:w-5 sm:h-5 shrink-0"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
    </svg>
    {{ status.label }}
  </span>
</template>
