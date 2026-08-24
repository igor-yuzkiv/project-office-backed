<script setup lang="ts">
import { useTemplateRef } from 'vue'
import { Icon } from '@iconify/vue'
import Popover from 'primevue/popover'
import IconPicker from './IconPicker.vue'

withDefaults(
    defineProps<{
        placeholder?: string
        clearLabel?: string
    }>(),
    { placeholder: 'Pick an icon', clearLabel: 'Remove icon' }
)

const icon = defineModel<string | null>({ required: true })

const popover = useTemplateRef<InstanceType<typeof Popover>>('popover')

function toggle(event: MouseEvent) {
    popover.value?.toggle(event)
}

function select(value: string) {
    icon.value = value
    popover.value?.hide()
}

function clear() {
    icon.value = null
}
</script>

<template>
    <div class="group w-11 relative shrink-0">
        <button
            type="button"
            class="border-surface-300 dark:border-surface-600 hover:border-surface-400 h-11 w-11 rounded-lg flex items-center justify-center border transition-colors"
            :aria-label="placeholder"
            @click="toggle"
        >
            <Icon v-if="icon" :icon="icon" class="text-surface-700 dark:text-surface-200 text-2xl" />
            <Icon v-else icon="tabler:square-rounded-plus" class="text-surface-400 text-xl" />
        </button>

        <!-- Clearing is a correction, not a field of its own: it appears over the icon
             it removes, and only when there is one to remove. -->
        <button
            v-if="icon"
            type="button"
            class="bg-surface-700 text-surface-0 hover:bg-surface-900 dark:bg-surface-600 dark:hover:bg-surface-400 h-4 w-4 -top-1 -right-1 absolute flex items-center justify-center rounded-full opacity-0 transition-opacity group-hover:opacity-100 focus:opacity-100"
            :title="clearLabel"
            :aria-label="clearLabel"
            @click.stop="clear"
        >
            <Icon icon="heroicons:x-mark" class="text-[10px]" />
        </button>
    </div>

    <Popover ref="popover">
        <IconPicker v-model="icon" @select="select" />
    </Popover>
</template>
