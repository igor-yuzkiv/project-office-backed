<script setup lang="ts">
import { Icon } from '@iconify/vue'

// The same white card carries a document sheet and, later, an editor or a metadata card;
// density is the one thing that differs between those uses.
withDefaults(defineProps<{ density?: 'comfortable' | 'compact'; expandable?: boolean }>(), {
    density: 'comfortable',
})

// Open unless the host says otherwise; the host may also bind it to remember or drive the fold.
const expanded = defineModel<boolean>('expanded', { default: true })
</script>

<template>
    <div
        class="rounded-xl bg-white dark:bg-surface-900 border-surface-200 dark:border-surface-700 content-card shadow-sm relative border"
        :class="density === 'compact' ? 'p-6' : 'p-10'"
    >
        <button
            v-if="expandable"
            type="button"
            class="right-3 top-3 text-surface-400 hover:text-surface-600 dark:hover:text-surface-200 absolute"
            :aria-expanded="expanded"
            :aria-label="expanded ? 'Collapse' : 'Expand'"
            @click="expanded = !expanded"
        >
            <Icon icon="heroicons:chevron-down" class="text-base transition-transform" :class="{ 'rotate-180': expanded }" />
        </button>

        <slot v-if="!expandable || expanded" />
        <slot v-else name="collapsed" />
    </div>
</template>

<!-- Not scoped: the markdown inside is rendered through v-html, so scoped attributes never reach it. -->
<style>
/* md-editor-v3 sets word-break: break-all on the preview, which snaps words mid-syllable.
   Long unbreakable tokens (urls, paths) still wrap, ordinary prose no longer does. */
.content-card .md-editor-preview,
.content-card .md-editor-preview :is(h1, h2, h3, h4, h5, h6) {
    word-break: normal;
    overflow-wrap: anywhere;
}

/* A code block or a wide table scrolls inside the card rather than widening it. Without this the
   card grows to fit its widest line and takes the whole layout with it. */
.content-card .md-editor-preview :is(pre, table) {
    max-width: 100%;
    overflow-x: auto;
}

/* md-editor-v3 gives the sticky code-block header z-index: 10000, which lands it above dialogs.
   Its own selector is three classes deep, so the override needs the card class to outrank it. */
.content-card .md-editor-preview .md-editor-code .md-editor-code-head {
    z-index: 1;
}
</style>
