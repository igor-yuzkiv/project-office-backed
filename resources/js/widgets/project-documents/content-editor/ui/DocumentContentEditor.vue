<script setup lang="ts">
import { ProjectDocumentAttachmentRoles, uploadProjectDocumentAttachmentRequest } from '@/entities/project-document'
import { DEFAULT_TOOLBARS, MarkdownEditor } from '@/shared/components/md-editor'

// The page already switches View / Edit / Annotate, so the editor's own fullscreen would only double it.
const TOOLBARS = DEFAULT_TOOLBARS.filter((name) => name !== 'fullscreen' && name !== 'pageFullscreen')

const props = defineProps<{ documentId: string }>()

const emit = defineEmits<{
    (e: 'save'): void
}>()

const content = defineModel<string>({ required: true })

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
    <!-- Takes the reader's place in the layout: the same column, the editor instead of the sheet. -->
    <MarkdownEditor
        v-model="content"
        class="min-h-0 flex-1"
        min-height="0"
        :toolbars="TOOLBARS"
        :mode-segment="false"
        :handle-image-upload="handleImageUpload"
        @save="emit('save')"
    />
</template>
