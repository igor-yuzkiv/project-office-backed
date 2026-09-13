<script setup lang="ts">
import { Icon } from '@iconify/vue'

withDefaults(
    defineProps<{
        moreLabel?: string
        lessLabel?: string
    }>(),
    { moreLabel: 'More', lessLabel: 'Less' }
)

const expanded = defineModel<boolean>('expanded', { default: false })

function toggle() {
    expanded.value = !expanded.value
}
</script>

<template>
    <div>
        <div class="properties-grid">
            <slot />
        </div>

        <div v-if="$slots.more" class="mt-1">
            <button
                type="button"
                class="gap-1 py-0.5 pr-2 type-meta-3 hover:text-ink inline-flex cursor-pointer items-center"
                :aria-expanded="expanded"
                @click="toggle"
            >
                <Icon :icon="expanded ? 'heroicons:chevron-up' : 'heroicons:chevron-down'" class="text-[12px]" />
                <span>{{ expanded ? lessLabel : moreLabel }}</span>
            </button>

            <div v-if="expanded" class="properties-grid mt-1">
                <slot name="more" />
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Mirrors `.props` in the redesign mockups: a label column of 104px, values fill the rest. */
.properties-grid {
    display: grid;
    grid-template-columns: 104px 1fr;
    row-gap: 2px;
}

.properties-grid > :deep(span) {
    padding: 5px 0;
    font-size: 13px;
    color: var(--color-ink-2);
}

.properties-grid > :deep(div) {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    min-height: 30px;
}
</style>
