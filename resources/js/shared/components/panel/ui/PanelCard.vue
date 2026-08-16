<script setup lang="ts">
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'

export type PanelCardState = 'pending' | 'error' | 'empty' | 'ready'

withDefaults(
    defineProps<{
        title: string
        subtitle?: string
        state: PanelCardState
        emptyMessage?: string
        errorMessage?: string
        skeletonRows?: number
    }>(),
    {
        subtitle: undefined,
        emptyMessage: 'Nothing here yet',
        errorMessage: 'Could not load data.',
        skeletonRows: 3,
    }
)

const emit = defineEmits<{
    (e: 'retry'): void
}>()
</script>

<template>
    <section
        class="border-surface-200 dark:border-surface-700 bg-surface-0 dark:bg-surface-900 rounded-xl overflow-hidden border"
    >
        <header
            class="border-surface-200 dark:border-surface-700 gap-3 px-4 py-3.5 flex items-baseline justify-between border-b"
        >
            <h2 class="text-surface-900 dark:text-surface-0 text-base font-semibold">{{ title }}</h2>
            <span v-if="subtitle" class="text-surface-400 text-xs">{{ subtitle }}</span>
            <slot name="action" />
        </header>

        <div v-if="state === 'pending'" class="gap-3 p-4 flex flex-col">
            <Skeleton v-for="n in skeletonRows" :key="n" height="2.5rem" />
        </div>

        <div v-else-if="state === 'error'" class="gap-3 p-8 flex flex-col items-center">
            <p class="text-surface-500 text-sm">{{ errorMessage }}</p>
            <Button label="Try again" size="small" severity="secondary" @click="emit('retry')" />
        </div>

        <p v-else-if="state === 'empty'" class="text-surface-400 p-8 text-sm text-center">{{ emptyMessage }}</p>

        <slot v-else />

        <div v-if="$slots.footer" class="border-surface-200 dark:border-surface-700 px-4 py-3 border-t">
            <slot name="footer" />
        </div>
    </section>
</template>
