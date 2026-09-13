<script setup lang="ts">
// The key (slot `key`, usually a copyable key), the `meta` note and the actions share the first
// row; the title has its own row below.
defineProps<{
    title: string
}>()
</script>

<template>
    <div class="gap-x-4 gap-y-1 grid grid-cols-[1fr_auto] items-center">
        <div class="type-meta min-w-0 flex items-center">
            <slot name="key" />
        </div>

        <div class="gap-1 flex shrink-0 items-center">
            <span v-if="$slots.meta" class="type-meta-3 pr-1.5 whitespace-nowrap">
                <slot name="meta" />
            </span>
            <slot name="actions" />
        </div>

        <h1 class="type-title col-span-full">{{ title }}</h1>
    </div>
</template>
