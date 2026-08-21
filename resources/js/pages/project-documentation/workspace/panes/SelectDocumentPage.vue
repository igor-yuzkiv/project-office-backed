<script setup lang="ts">
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'

const emit = defineEmits<{
    (e: 'create-document'): void
}>()

const hints = [
    { action: 'Open a document', where: 'click it in the tree' },
    { action: 'Nest a document', where: 'node menu → New document inside' },
    { action: 'Delete a document', where: 'node menu → Delete' },
]
</script>

<template>
    <div class="gap-6 p-10 flex flex-1 flex-col items-center justify-center">
        <Icon icon="heroicons:book-open" class="text-surface-200 dark:text-surface-800 text-8xl" />

        <div class="gap-1.5 flex flex-col items-center text-center">
            <p class="text-surface-700 dark:text-surface-200 text-lg font-medium">Project documentation</p>
            <p class="text-surface-500 max-w-sm text-sm">
                Pick a document in the tree on the left to read it here, or start a new one.
            </p>
        </div>

        <Button label="New document" size="small" @click="emit('create-document')">
            <template #icon>
                <Icon icon="material-symbols:add" class="text-base" />
            </template>
        </Button>

        <dl class="gap-y-2 gap-x-6 text-surface-400 text-xs grid grid-cols-[auto_auto] items-baseline">
            <template v-for="hint in hints" :key="hint.action">
                <dt class="text-surface-500 dark:text-surface-300 text-right">{{ hint.action }}</dt>
                <dd>{{ hint.where }}</dd>
            </template>
        </dl>
    </div>
</template>
