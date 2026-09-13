<script setup lang="ts">
import { computed } from 'vue'
import { useClipboard } from '@vueuse/core'
import { Icon } from '@iconify/vue'

// Copies the markdown source, not the rendered HTML. Every read-only markdown surface renders
// through MarkdownPreview, so this one button is "copy any markdown" for the whole app.
const props = defineProps<{ source: string }>()

const { copy, copied } = useClipboard()
const canCopy = computed(() => props.source.trim().length > 0)
</script>

<template>
    <!-- Stops the click so a host that picks blocks on click (DocumentSheet) does not treat
         copying as picking. `data-copied` lets a hover-only host keep the check mark visible. -->
    <button
        v-if="canCopy"
        type="button"
        class="text-ink-3 hover:text-ink transition-colors"
        :data-copied="copied || undefined"
        title="Copy markdown"
        aria-label="Copy markdown"
        @click.stop="copy(source)"
    >
        <Icon :icon="copied ? 'mdi:check' : 'tabler:copy'" class="text-base" />
    </button>
</template>
