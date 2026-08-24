<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Icon, loadIcon } from '@iconify/vue'
import type { ComponentSize } from '@/shared/types'
import { PROJECT_ICON_SIZE_MAP, projectTintClass } from '../project-icon.config'

const props = withDefaults(
    defineProps<{
        prefix: string
        icon?: string | null
        size?: ComponentSize
    }>(),
    { size: 'medium' }
)

const sizeClasses = computed(() => PROJECT_ICON_SIZE_MAP[props.size])

// The prefix carries the tint whether or not an icon was chosen, so a project keeps the same
// colour on the day someone gives it one.
const tintClass = computed(() => projectTintClass(props.prefix))

// A name can be typed by hand, so it can also be wrong. An icon that never resolves would
// otherwise leave a blank plate; the prefix takes over instead.
const isIconResolved = ref(false)

watch(
    () => props.icon,
    async (name) => {
        isIconResolved.value = false

        if (!name) return

        try {
            await loadIcon(name)
            if (props.icon === name) isIconResolved.value = true
        } catch {
            isIconResolved.value = false
        }
    },
    { immediate: true }
)
</script>

<template>
    <span class="rounded-lg flex items-center justify-center" :class="[sizeClasses.root, tintClass]">
        <Icon v-if="icon && isIconResolved" :icon="icon" :class="sizeClasses.glyph" />
        <span v-else class="font-semibold" :class="sizeClasses.label">{{ prefix }}</span>
    </span>
</template>
