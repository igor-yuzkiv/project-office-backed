<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { useCopyableKey } from '../composables/use.copyable-key'

const props = withDefaults(
    defineProps<{
        value: string
        size?: 'sm' | 'md'
    }>(),
    { size: 'sm' }
)

const { copyKey, copied } = useCopyableKey(() => props.value)
</script>

<template>
    <button
        type="button"
        :class="[
            'group/key gap-1.5 rounded px-1 -ml-1 hover:bg-hover hover:text-ink inline-flex shrink-0 cursor-pointer items-center',
            size === 'md' ? 'text-ink-2 text-[13px]' : 'type-meta',
        ]"
        :title="`Copy ${value}`"
        @click.stop="copyKey"
    >
        <span>{{ value }}</span>
        <Icon
            :icon="copied ? 'mdi:check' : 'tabler:copy'"
            :class="[
                'shrink-0 transition-opacity',
                size === 'md' ? 'text-[13px]' : 'text-[12px]',
                copied ? 'opacity-100' : 'opacity-0 group-hover/key:opacity-100',
            ]"
        />
    </button>
</template>
