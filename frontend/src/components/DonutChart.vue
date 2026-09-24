<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    /** 0-100 */
    percent: number
    color?: string
    trackColor?: string
    size?: number
    strokeWidth?: number
  }>(),
  {
    color: '#1a6838',
    trackColor: '#e5e7eb',
    size: 128,
    strokeWidth: 3.5,
  },
)

const dash = computed(() => `${Math.max(0, Math.min(100, props.percent))}, 100`)
</script>

<template>
  <div class="relative inline-flex items-center justify-center" :style="{ width: `${size}px`, height: `${size}px` }">
    <svg viewBox="0 0 36 36" class="-rotate-90 w-full h-full">
      <circle cx="18" cy="18" r="15.9155" fill="none" :stroke="trackColor" :stroke-width="strokeWidth" />
      <circle
        v-if="percent > 0"
        cx="18"
        cy="18"
        r="15.9155"
        fill="none"
        :stroke="color"
        :stroke-width="strokeWidth"
        stroke-linecap="round"
        :stroke-dasharray="dash"
      />
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center">
      <slot>
        <span class="text-2xl font-bold text-gray-900">{{ Math.round(percent) }}%</span>
      </slot>
    </div>
  </div>
</template>
