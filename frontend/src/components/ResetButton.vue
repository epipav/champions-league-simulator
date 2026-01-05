<template>
  <button @click="handleClick" :disabled="loading" :class="buttonClass">
    {{ buttonText }}
  </button>
</template>

<script setup lang="ts">
import { computed, type ComputedRef } from 'vue'

interface Props {
  loading?: boolean
  isInitialized?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  isInitialized: false,
})

const emit = defineEmits<{
  initialize: []
  reset: []
}>()

const buttonClass: ComputedRef<string> = computed(() => {
  const base =
    'py-2 px-4 rounded disabled:bg-gray-400 disabled:cursor-not-allowed transition text-sm text-white'
  return props.isInitialized
    ? `${base} bg-red-600 hover:bg-red-700`
    : `${base} bg-green-600 hover:bg-green-700`
})

const buttonText: ComputedRef<string> = computed(() => {
  if (props.loading) {
    return props.isInitialized ? 'Resetting...' : 'Starting...'
  }
  return props.isInitialized ? 'Reset League' : 'Start League'
})

const handleClick = (): void => {
  if (props.isInitialized) {
    if (confirm('Are you sure you want to reset the league? All match results will be lost.')) {
      emit('reset')
    }
  } else {
    emit('initialize')
  }
}
</script>
