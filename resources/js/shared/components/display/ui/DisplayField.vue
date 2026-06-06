<script setup lang="ts">
withDefaults(
    defineProps<{
        label: string
        value?: string | null
        inline?: boolean
    }>(),
    { inline: false }
)
</script>

<template>
    <div :class="inline ? 'flex-row items-center gap-2' : 'flex-col gap-1'" class="flex">
        <span class="text-surface-400 shrink-0">{{ label }}:</span>
        <span class="text-surface-700">{{ value ?? '—' }}</span>
    </div>
</template>
