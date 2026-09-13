<script setup lang="ts">
export type MarkdownEditorMode = 'write' | 'preview' | 'split'

// Rendered through md-editor-v3's `defToolbars` slot, which clones the node with its own
// props (theme, insert, …); none of them belong on the buttons, hence no attribute fallthrough.
defineOptions({ inheritAttrs: false })

const mode = defineModel<MarkdownEditorMode>({ required: true })

const OPTIONS: { value: MarkdownEditorMode; label: string }[] = [
    { value: 'write', label: 'Write' },
    { value: 'preview', label: 'Preview' },
    { value: 'split', label: 'Split' },
]
</script>

<template>
    <div class="gap-0.5 ml-1 flex items-center text-[13px]" role="group" aria-label="Editor mode">
        <button
            v-for="option in OPTIONS"
            :key="option.value"
            type="button"
            class="px-2 py-0.5 rounded-[5px] transition-colors"
            :class="option.value === mode ? 'bg-hover text-ink' : 'text-ink-2 hover:text-ink hover:bg-hover'"
            :aria-pressed="option.value === mode"
            @click="mode = option.value"
        >
            {{ option.label }}
        </button>
    </div>
</template>
