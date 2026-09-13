<script setup lang="ts">
import { RouterLink, useRoute } from 'vue-router'
import HeaderActionButton from './HeaderActionButton.vue'
import type { BreadcrumbItem, HeaderAction } from '../../types'

defineProps<{
    actions?: HeaderAction[]
    breadcrumbs?: BreadcrumbItem[]
}>()

const route = useRoute()
</script>

<template>
    <header class="h-11 border-line px-4 text-ink-2 flex shrink-0 items-center justify-between border-b text-[13px]">
        <nav v-if="breadcrumbs?.length" class="gap-1 min-w-0 flex items-center" aria-label="Breadcrumb">
            <template v-for="(item, index) in breadcrumbs" :key="index">
                <span v-if="index > 0" class="text-ink-3" aria-hidden="true">/</span>
                <RouterLink
                    v-if="item.to"
                    :to="item.to"
                    class="px-1.5 hover:bg-hover hover:text-ink rounded-[5px] py-[3px] whitespace-nowrap transition-colors"
                >
                    {{ item.label }}
                </RouterLink>
                <span v-else class="px-1.5 text-ink font-medium truncate py-[3px]">{{ item.label }}</span>
            </template>
        </nav>

        <span v-else class="px-1.5 text-ink font-medium truncate">{{ route.meta.title }}</span>

        <div class="gap-1 flex shrink-0 items-center">
            <HeaderActionButton v-if="actions?.length" :actions="actions" />
        </div>
    </header>
</template>
