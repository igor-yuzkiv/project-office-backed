<script setup lang="ts">
import type { ToolbarNames } from 'md-editor-v3'
import { ProjectDocumentAttachmentRoles, uploadProjectDocumentAttachmentRequest } from '@/entities/project-document'
import { ContentCard } from '@/shared/components/content-card'
import { DEFAULT_TOOLBARS, MarkdownEditor } from '@/shared/components/md-editor'

const props = defineProps<{ documentId: string }>()

const emit = defineEmits<{
    (e: 'save'): void
}>()

const content = defineModel<string>({ required: true })

// The sheet is the preview, so the editor's own preview, split view and catalog would only
// double it; what stays is writing, images and a way to get more room.
const DOUBLED_BY_THE_SHEET: ToolbarNames[] = ['catalog', 'preview', 'previewOnly', 'fullscreen']
const TOOLBARS = DEFAULT_TOOLBARS.filter((name) => !DOUBLED_BY_THE_SHEET.includes(name))

async function handleImageUpload(files: File[], callback: (urls: string[]) => void) {
    const results = await Promise.all(
        files.map((file) =>
            uploadProjectDocumentAttachmentRequest(props.documentId, file, ProjectDocumentAttachmentRoles.CONTENT)
        )
    )
    callback(results.map((result) => result.data.url))
}
</script>

<template>
    <!-- The same sheet as the reader's, filled with an editor instead of the rendered text. -->
    <ContentCard class="min-h-0 flex flex-1 flex-col" density="compact">
        <MarkdownEditor
            v-model="content"
            class="min-h-0 flex-1"
            :toolbars="TOOLBARS"
            min-height="0"
            :handle-image-upload="handleImageUpload"
            @save="emit('save')"
        />
    </ContentCard>
</template>
