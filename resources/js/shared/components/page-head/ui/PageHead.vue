<script setup lang="ts">
export type PageHeadMode = 'list' | 'document'

withDefaults(
    defineProps<{
        title: string
        /**
         * `list` puts the title and the actions on one row. `document` puts the key (slot `key`,
         * usually a copyable key), the `meta` note and the actions on the first row and the
         * title on its own row below.
         */
        mode?: PageHeadMode
    }>(),
    {
        mode: 'list',
    }
)
</script>

<template>
    <div class="gap-x-4 grid grid-cols-[1fr_auto]" :class="mode === 'list' ? 'items-start' : 'gap-y-1 items-center'">
        <div v-if="mode === 'list'" class="min-w-0">
            <h1 class="type-page truncate">{{ title }}</h1>
            <p v-if="$slots.lede" class="type-meta mt-0.5">
                <slot name="lede" />
            </p>
        </div>
        <div v-else class="type-meta min-w-0 flex items-center">
            <slot name="key" />
        </div>

        <div class="gap-1 flex shrink-0 items-center">
            <span v-if="mode === 'document' && $slots.meta" class="type-meta-3 pr-1.5 whitespace-nowrap">
                <slot name="meta" />
            </span>
            <slot name="actions" />
        </div>

        <h1 v-if="mode === 'document'" class="type-title col-span-full">{{ title }}</h1>
    </div>
</template>
