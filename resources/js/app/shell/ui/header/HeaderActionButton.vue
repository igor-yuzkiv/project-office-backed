<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import Button from 'primevue/button'
import SplitButton from 'primevue/splitbutton'
import type { MenuItem } from 'primevue/menuitem'
import type { HeaderAction } from '../../types'

const props = defineProps<{
    actions: HeaderAction[]
}>()

const router = useRouter()

const primaryAction = computed<HeaderAction | undefined>(() => {
    return props.actions.find((a) => a.is_primary) ?? props.actions[0]
})

const dropdownItems = computed<MenuItem[]>(() => {
    if (!primaryAction.value) return []
    return props.actions
        .filter((a) => a.key !== primaryAction.value!.key)
        .map((a) => ({
            label: a.title,
            command: a.to ? () => router.push(a.to!) : a.action,
        }))
})

const isSingle = computed(() => props.actions.length === 1)
</script>

<template>
    <template v-if="primaryAction && isSingle">
        <Button
            v-if="primaryAction.to"
            :as="RouterLink"
            :to="primaryAction.to"
            :label="primaryAction.title"
            size="small"
        />
        <Button v-else :label="primaryAction.title" size="small" @click="primaryAction.action?.()" />
    </template>

    <template v-else-if="primaryAction">
        <SplitButton
            v-if="primaryAction.to"
            :as="RouterLink"
            :to="primaryAction.to"
            :label="primaryAction.title"
            :model="dropdownItems"
            size="small"
        />
        <SplitButton
            v-else
            :label="primaryAction.title"
            :model="dropdownItems"
            size="small"
            @click="primaryAction.action?.()"
        />
    </template>
</template>
