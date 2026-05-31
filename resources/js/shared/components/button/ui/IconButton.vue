<script setup lang="ts">
import Button from 'primevue/button'
import type { ButtonProps } from 'primevue/button'
import { Icon } from '@iconify/vue'
import { computed } from 'vue'
import type { ButtonSize } from '../button.types'
import { BUTTON_SIZE_MAP, ICON_SIZE_MAP } from '../button.config'

interface IconButtonProps extends /* @vue-ignore */ Omit<ButtonProps, 'size'> {
    icon: string
    loading?: boolean
    loadingIcon?: string
    size?: ButtonSize
}

const props = withDefaults(defineProps<IconButtonProps>(), {
    severity: 'secondary',
    loading: false,
    loadingIcon: 'line-md:loading-loop',
    size: 'small',
})

function resolveFromMap(size: ButtonSize, map: Record<ButtonSize, string>): string {
    return map[size] ?? ''
}

const iconSize = computed<string>(() => resolveFromMap(props.size ?? 'small', ICON_SIZE_MAP))
const buttonSize = computed<string>(() => resolveFromMap(props.size ?? 'small', BUTTON_SIZE_MAP))
</script>

<template>
    <Button
        :class="['flex shrink-0 grow-0 items-center justify-center', buttonSize]"
        v-bind="{ ...$attrs, size: '' }"
        text
        rounded
    >
        <template #icon>
            <Icon v-if="loading" :icon="loadingIcon" :class="iconSize" />
            <Icon v-else :icon="icon" :class="iconSize" />
        </template>
    </Button>
</template>
