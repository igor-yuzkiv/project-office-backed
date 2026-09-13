<script setup lang="ts">
import { computed, useId } from 'vue'
import Dialog from 'primevue/dialog'
import { MdPreview } from 'md-editor-v3'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'
import MarkdownCopyButton from './MarkdownCopyButton.vue'

// The same markdown, rendered on a reading sheet that takes almost the whole window. The host
// preview stays where it is; this only gives the reader room.
defineProps<{ modelValue: string }>()

const visible = defineModel<boolean>('visible', { required: true })

const themeStore = useAppThemeStore()
// md-editor-v3 keys its instances by editorId, so the sheet needs one of its own.
const editorId = useId()

const previewTheme = computed<'dark' | 'light'>(() => (themeStore.isDark ? 'dark' : 'light'))
</script>

<template>
    <Dialog
        v-model:visible="visible"
        modal
        dismissable-mask
        :pt="{ root: { class: 'w-[80vw] h-[92vh]' } }"
    >
        <template #header>
            <div class="gap-3 flex items-center">
                <span class="type-section">Preview</span>
                <MarkdownCopyButton :source="modelValue" />
            </div>
        </template>

        <MdPreview
            :editor-id="editorId"
            :model-value="modelValue"
            language="en-US"
            :theme="previewTheme"
            :code-foldable="false"
            preview-theme="github"
            class="type-prose"
        />
    </Dialog>
</template>
