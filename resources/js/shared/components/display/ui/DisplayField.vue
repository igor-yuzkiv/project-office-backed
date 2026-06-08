<script setup lang="ts">
import { computed } from 'vue'
import { format as dateFnsFormat } from 'date-fns'

const props = withDefaults(
    defineProps<{
        label?: string
        value?: string | null
        inline?: boolean
        emptyValue?: string
        format?: string
    }>(),
    { inline: false, emptyValue: 'N/A' }
)

const displayValue = computed(() => {
    if (props.value == null) return null
    if (!props.format) return props.value
    try {
        return dateFnsFormat(new Date(props.value), props.format)
    } catch {
        return props.value
    }
})
</script>

<template>
    <div :class="inline ? 'gap-2 flex-row items-center' : 'gap-1 flex-col'" class="flex">
        <span v-if="label || $slots.label" class="text-surface-400 shrink-0">
            <slot name="label">{{ label }}</slot>
        </span>
        <slot>
            <span class="text-surface-700">{{ displayValue ?? emptyValue }}</span>
        </slot>
    </div>
</template>
