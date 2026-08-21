<script setup lang="ts">
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'

const isOpen = defineModel<boolean>('open', { required: true })
</script>

<template>
    <section class="flex h-full flex-col overflow-hidden">
        <header
            class="border-surface-200 dark:border-surface-700 gap-2 px-3 py-2 flex items-center border-b"
            :class="isOpen ? 'justify-between' : 'justify-center'"
        >
            <h2
                v-if="isOpen"
                class="text-surface-600 dark:text-surface-300 text-xs font-semibold tracking-wide uppercase"
            >
                Document details
            </h2>

            <Button
                severity="secondary"
                text
                rounded
                size="small"
                :title="isOpen ? 'Hide details' : 'Show details'"
                :aria-label="isOpen ? 'Hide details' : 'Show details'"
                @click="isOpen = !isOpen"
            >
                <template #icon>
                    <Icon
                        :icon="isOpen ? 'heroicons:chevron-double-right' : 'heroicons:chevron-double-left'"
                        class="text-base"
                    />
                </template>
            </Button>
        </header>

        <div v-if="isOpen" class="flex-1 overflow-auto">
            <slot />
        </div>
    </section>
</template>
