<script setup lang="ts">
import { computed } from 'vue'

interface Point {
  label: string
  persen: number
}

const props = defineProps<{
  points: Point[]
}>()

const width = 600
const height = 180
const padTop = 16
const padBottom = 28
const padX = 28
const plotHeight = height - padTop - padBottom

const scaleX = (i: number) => {
  if (props.points.length <= 1) return padX
  return padX + (i / (props.points.length - 1)) * (width - padX * 2)
}
const scaleY = (persen: number) => padTop + (1 - Math.min(100, Math.max(0, persen)) / 100) * plotHeight

const dots = computed(() => props.points.map((p, i) => ({ ...p, x: scaleX(i), y: scaleY(p.persen) })))

const linePath = computed(() => dots.value.map((d, i) => `${i === 0 ? 'M' : 'L'} ${d.x} ${d.y}`).join(' '))

const areaPath = computed(() => {
  if (dots.value.length === 0) return ''
  const first = dots.value[0]
  const last = dots.value[dots.value.length - 1]
  return `${linePath.value} L ${last.x} ${padTop + plotHeight} L ${first.x} ${padTop + plotHeight} Z`
})

const gridLines = [0, 40, 60, 80, 90, 100]

const labelStep = computed(() => Math.max(1, Math.ceil(props.points.length / 8)))
</script>

<template>
  <svg :viewBox="`0 0 ${width} ${height}`" class="w-full h-auto" preserveAspectRatio="none" role="img" aria-label="Grafik tren skor K3">
    <line
      v-for="g in gridLines"
      :key="g"
      :x1="padX"
      :x2="width - padX"
      :y1="scaleY(g)"
      :y2="scaleY(g)"
      stroke="#e5e7eb"
      stroke-width="1"
      :stroke-dasharray="g === 0 ? '0' : '3,3'"
    />
    <text v-for="g in gridLines" :key="`label-${g}`" :x="2" :y="scaleY(g) + 3" font-size="9" fill="#9ca3af">{{ g }}%</text>

    <path v-if="dots.length > 1" :d="areaPath" fill="#1f8143" fill-opacity="0.08" stroke="none" />
    <path v-if="dots.length > 1" :d="linePath" fill="none" stroke="#1f8143" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

    <g v-for="(d, i) in dots" :key="i">
      <circle :cx="d.x" :cy="d.y" r="3.5" fill="#1f8143" stroke="white" stroke-width="1.5">
        <title>{{ d.label }}: {{ d.persen }}%</title>
      </circle>
      <text
        v-if="i % labelStep === 0"
        :x="d.x"
        :y="height - 6"
        font-size="9"
        fill="#6b7280"
        text-anchor="middle"
      >
        {{ d.label }}
      </text>
    </g>
  </svg>
</template>
