<script setup lang="ts">
import { computed } from 'vue'
import Button from 'primevue/button'
import SplitButton from 'primevue/splitbutton'
import type { MenuItem } from 'primevue/menuitem'
import type { HeaderAction } from '../../types'

const props = defineProps<{
    actions: HeaderAction[]
}>()

const primaryAction = computed<HeaderAction | undefined>(() => {
    return props.actions.find((a) => a.is_primary) ?? props.actions[0]
})

const dropdownItems = computed<MenuItem[]>(() => {
    if (!primaryAction.value) return []
    return props.actions
        .filter((a) => a.key !== primaryAction.value!.key)
        .map((a) => ({ label: a.title, command: a.action }))
})

const isSingle = computed(() => props.actions.length === 1)
</script>

<template>
    <Button
        v-if="primaryAction && isSingle"
        :label="primaryAction.title"
        size="small"
        @click="primaryAction.action"
    />

    <SplitButton
        v-else-if="primaryAction"
        :label="primaryAction.title"
        :model="dropdownItems"
        size="small"
        @click="primaryAction.action"
    />
</template>
