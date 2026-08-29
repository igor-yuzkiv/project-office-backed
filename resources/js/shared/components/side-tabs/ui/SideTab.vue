<script setup lang="ts">
import { computed, inject, onScopeDispose } from 'vue'
import { SIDE_TABS_CONTEXT } from '../side-tabs.context'

const props = defineProps<{
    value: string
    icon: string
    label: string
}>()

const tabs = inject(SIDE_TABS_CONTEXT)

if (!tabs) throw new Error('SideTab must be rendered inside SideTabs')

tabs.register({ value: props.value, icon: props.icon, label: props.label })

onScopeDispose(() => tabs.unregister(props.value))

// An inactive tab is not mounted at all: whatever it holds — queries, sessions — must not run
// for a panel nobody is looking at.
const isShown = computed(() => tabs.contentVisible.value && tabs.active.value === props.value)
</script>

<template>
    <slot v-if="isShown" />
</template>
