<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { RouterLink } from 'vue-router'
import type { RouteLocationRaw } from 'vue-router'

defineProps<{
    label: string
    to: RouteLocationRaw
}>()
</script>

<template>
    <RouterLink :to="to" class="text-primary gap-1 text-sm font-semibold inline-flex items-center">
        {{ label }}
        <Icon icon="heroicons:chevron-right" class="size-3.5" aria-hidden="true" />
    </RouterLink>
</template>
