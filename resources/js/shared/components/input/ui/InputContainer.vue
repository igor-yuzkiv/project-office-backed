<script setup lang="ts">
import { computed } from 'vue'
import type { Maybe } from '@/shared/types'

const props = defineProps<{
    label?: string
    error?: Maybe<string | string[]>
    required?: boolean
}>()

const errorMessage = computed(() => {
    if (!props.error) return null
    return Array.isArray(props.error) ? props.error[0] : props.error
})
</script>

<template>
    <div class="gap-1 flex flex-col">
        <span v-if="label" class="text-xs font-medium text-surface-400 tracking-wide uppercase">
            {{ label }}<span v-if="required" class="text-red-500 ml-0.5">*</span>
        </span>
        <slot />
        <span v-if="errorMessage" class="text-xs text-red-500">{{ errorMessage }}</span>
    </div>
</template>
