<script setup lang="ts">
import { useId } from 'vue'
import { useLocalStorage } from '@vueuse/core'
import { Icon } from '@iconify/vue'

const props = withDefaults(
    defineProps<{
        title: string
        /** localStorage key the collapsed flag is kept under, one per section. */
        storageKey: string
        /** Margins of the heading, since they depend on where the section sits. */
        headingClass?: string
    }>(),
    { headingClass: 'mb-2.5' }
)

const collapsed = useLocalStorage(props.storageKey, false)
const bodyId = useId()
</script>

<template>
    <h2 class="type-section flex items-baseline" :class="[headingClass, $slots.aside && 'gap-2']">
        <button
            type="button"
            class="gap-1.5 hover:text-ink-2 inline-flex cursor-pointer items-center"
            :aria-expanded="!collapsed"
            :aria-controls="bodyId"
            @click="collapsed = !collapsed"
        >
            <Icon
                :icon="collapsed ? 'heroicons:chevron-right' : 'heroicons:chevron-down'"
                class="text-ink-3 text-[12px]"
            />
            {{ title }}
        </button>
        <slot name="aside" />
    </h2>
    <div v-show="!collapsed" :id="bodyId">
        <slot />
    </div>
</template>
