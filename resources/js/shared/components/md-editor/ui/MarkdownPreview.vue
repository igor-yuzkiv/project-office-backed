<script setup lang="ts">
import { computed, ref, useId } from 'vue'
import { MdPreview } from 'md-editor-v3'
import 'md-editor-v3/lib/preview.css'
import { Icon } from '@iconify/vue'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'
import MarkdownCopyButton from './MarkdownCopyButton.vue'
import MarkdownExpandDialog from './MarkdownExpandDialog.vue'

const props = defineProps<{ modelValue: string }>()

const emit = defineEmits<{
    (e: 'htmlChanged'): void
}>()

const themeStore = useAppThemeStore()
const editorId = useId()

const rootRef = ref<HTMLElement>()

const previewTheme = computed<'dark' | 'light'>(() => (themeStore.isDark ? 'dark' : 'light'))

const expanded = ref(false)

const hasSource = computed(() => props.modelValue.trim().length > 0)

// The rendered markdown lives in md-editor-v3's own container; consumers that decorate blocks
// need that element, not this wrapper.
function getPreviewRoot(): HTMLElement | null {
    return rootRef.value?.querySelector('.md-editor-preview') ?? null
}

defineExpose({ getPreviewRoot })
</script>

<template>
    <div ref="rootRef" class="group relative">
        <!-- Copy and Expand show on hover, like a margin note, and stay visible once copied so
             the check mark is seen. Expand stops the click so a host that picks blocks on click
             (DocumentSheet) does not treat it as picking. -->
        <div
            v-if="hasSource"
            class="gap-2 right-0 top-0 absolute z-[1] flex items-center opacity-0 transition-opacity group-hover:opacity-100 focus-within:opacity-100 has-[[data-copied]]:opacity-100"
        >
            <MarkdownCopyButton :source="modelValue" />
            <button
                type="button"
                class="text-ink-3 hover:text-ink transition-colors"
                title="Expand"
                aria-label="Expand"
                @click.stop="expanded = true"
            >
                <Icon icon="tabler:arrows-maximize" class="text-base" />
            </button>
        </div>
        <MdPreview
            :editor-id="editorId"
            :model-value="modelValue"
            language="en-US"
            :theme="previewTheme"
            :code-foldable="false"
            preview-theme="github"
            @on-html-changed="() => emit('htmlChanged')"
        />
        <MarkdownExpandDialog v-model:visible="expanded" :model-value="modelValue" />
    </div>
</template>
