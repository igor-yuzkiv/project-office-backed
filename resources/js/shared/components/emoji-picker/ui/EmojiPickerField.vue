<script setup lang="ts">
import { useTemplateRef } from 'vue'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Popover from 'primevue/popover'
import EmojiPicker from './EmojiPicker.vue'

withDefaults(
    defineProps<{
        placeholder?: string
        clearLabel?: string
    }>(),
    { placeholder: 'Pick an emoji', clearLabel: 'Remove' }
)

const emoji = defineModel<string | null>({ required: true })

const popover = useTemplateRef<InstanceType<typeof Popover>>('popover')

function toggle(event: MouseEvent) {
    popover.value?.toggle(event)
}

function select(value: string) {
    emoji.value = value
    popover.value?.hide()
}

function clear() {
    emoji.value = null
    popover.value?.hide()
}
</script>

<template>
    <div class="gap-2 flex items-center">
        <button
            type="button"
            class="border-surface-300 dark:border-surface-600 hover:border-surface-400 h-11 w-11 rounded-lg text-2xl flex shrink-0 items-center justify-center border transition-colors"
            :aria-label="placeholder"
            @click="toggle"
        >
            <span v-if="emoji">{{ emoji }}</span>
            <Icon v-else icon="heroicons:face-smile" class="text-surface-400 text-xl" />
        </button>

        <Button v-if="emoji" :label="clearLabel" size="small" severity="secondary" text @click="clear" />
    </div>

    <Popover ref="popover">
        <div class="gap-2 flex flex-col">
            <EmojiPicker @select="select" />
            <Button v-if="emoji" :label="clearLabel" size="small" severity="secondary" text @click="clear" />
        </div>
    </Popover>
</template>
