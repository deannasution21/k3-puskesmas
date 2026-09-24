<script setup lang="ts">
import { computed } from 'vue'
import { formatPeriode } from '../lib/periode'

const props = defineProps<{
  modelValue: { bulan: number; tahun: number }
  monthsBack?: number
}>()

const emit = defineEmits<{
  'update:modelValue': [value: { bulan: number; tahun: number }]
}>()

const options = computed(() => {
  const count = props.monthsBack ?? 12
  const now = new Date()
  const list: { key: string; bulan: number; tahun: number; label: string }[] = []

  for (let i = 0; i < count; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
    const bulan = d.getMonth() + 1
    const tahun = d.getFullYear()
    list.push({ key: `${tahun}-${bulan}`, bulan, tahun, label: formatPeriode(bulan, tahun) })
  }

  return list
})

function onChange(event: Event) {
  const [tahun, bulan] = (event.target as HTMLSelectElement).value.split('-').map(Number)
  emit('update:modelValue', { bulan, tahun })
}
</script>

<template>
  <select
    :value="`${modelValue.tahun}-${modelValue.bulan}`"
    class="text-sm font-medium border border-gray-300 rounded-lg px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
    @change="onChange"
  >
    <option v-for="opt in options" :key="opt.key" :value="opt.key">{{ opt.label }}</option>
  </select>
</template>
