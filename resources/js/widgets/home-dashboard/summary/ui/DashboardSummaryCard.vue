<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { RouterLink } from 'vue-router'
import { SUMMARY_BANNER_ACCENT_CLASSES, type SummaryBanner } from '../config'

defineProps<{ banner: SummaryBanner }>()
</script>

<template>
    <RouterLink
        :to="banner.to"
        class="border-surface-200 dark:border-surface-700 bg-surface-0 dark:bg-surface-900 hover:border-primary focus-visible:outline-primary gap-3 p-3.5 rounded-xl flex border focus-visible:outline-2 focus-visible:outline-offset-2"
    >
        <span
            class="size-9 rounded-lg grid shrink-0 place-items-center"
            :class="SUMMARY_BANNER_ACCENT_CLASSES[banner.accent]"
            aria-hidden="true"
        >
            <Icon :icon="banner.icon" class="size-5" />
        </span>

        <span class="min-w-0 flex flex-col">
            <span class="text-surface-900 dark:text-surface-0 text-2xl leading-tight font-semibold">
                {{ banner.count }}
            </span>
            <span class="text-surface-700 dark:text-surface-200 text-sm font-medium">{{ banner.label }}</span>
            <span v-if="banner.description" class="text-surface-400 mt-1 text-xs">{{ banner.description }}</span>
        </span>
    </RouterLink>
</template>
