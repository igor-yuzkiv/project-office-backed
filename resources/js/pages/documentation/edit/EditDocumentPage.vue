<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import {
    ProjectDocumentAttachmentRoles,
    uploadProjectDocumentAttachmentRequest,
    useProjectDocumentQuery,
    useUpdateProjectDocumentMutation,
} from '@/entities/project-document'
import { projectDocumentStatusOptions } from '@/entities/project-document/config'
import type { IUpdateProjectDocumentInput, ProjectDocumentStatusValue } from '@/entities/project-document/types'
import type { ITag } from '@/entities/tag/types'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { TagList } from '@/widgets/tags/metadata'
import { IconButton } from '@/shared/components/button'
import { InputContainer } from '@/shared/components/input'
import { MarkdownEditor } from '@/shared/components/md-editor'
import { ApiError } from '@/shared/api/api.error'
import { useToast } from '@/shared/composables'
import type { LaravelValidationErrors } from '@/shared/types'
import { useBreadcrumbs, useHeaderActions } from '@/app/shell'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'

interface DocumentEditFormData {
    title: string
    content: string
    status: ProjectDocumentStatusValue
    tags: ITag[]
}

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()

const projectId = route.params.projectId as string
const documentId = route.params.documentId as string

const { projectDocument, isError } = useProjectDocumentQuery(documentId, { with_path: true })
const { mutate: updateDocument, isPending: isSaving } = useUpdateProjectDocumentMutation()

const openedDocument = computed(() =>
    projectDocument.value?.project_id === projectId ? projectDocument.value : undefined
)

const formData = ref<DocumentEditFormData>({ title: '', content: '', status: 'draft', tags: [] })
const isFormInitialized = ref(false)
const validationErrors = ref<LaravelValidationErrors>({})
const showManageTagsDialog = ref(false)

function openView() {
    router.replace({
        name: 'project-documentation.document',
        params: { projectId, documentId },
    })
}

function handleError(error: unknown) {
    if (error instanceof ApiError && error.isValidationError) {
        validationErrors.value = error.validationErrors ?? {}
        toast.error(Object.values(validationErrors.value).flat()[0] ?? 'Document could not be saved.')
    } else {
        toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to save document.')
    }
}

function submit() {
    const document = openedDocument.value

    if (!document || isSaving.value) return

    // A document has to keep a name. An empty title is refused rather than sent, and the
    // heading goes back to the name the server still knows.
    if (!formData.value.title.trim()) {
        formData.value.title = document.title
        toast.warn('A document title cannot be empty.')

        return
    }

    validationErrors.value = {}

    const input: IUpdateProjectDocumentInput = {
        title: formData.value.title.trim(),
        content: formData.value.content,
        status: formData.value.status,
        tag_ids: formData.value.tags.map((tag) => tag.id),
    }

    updateDocument(
        { id: document.id, data: input },
        {
            onSuccess: () => {
                toast.success('Document saved.')
                openView()
            },
            // The draft survives a failed save: the user keeps what they typed.
            onError: handleError,
        }
    )
}

async function handleImageUpload(files: File[], callback: (urls: string[]) => void) {
    const results = await Promise.all(
        files.map((file) =>
            uploadProjectDocumentAttachmentRequest(documentId, file, ProjectDocumentAttachmentRoles.CONTENT)
        )
    )
    callback(results.map((result) => result.data.url))
}

// Not found, wrong project, failed request — the workspace already says all three, so the
// editor hands those cases back instead of showing an empty form.
watch([isError, projectDocument], () => {
    if (isError.value || (projectDocument.value && !openedDocument.value)) openView()
})

watch(
    openedDocument,
    (document) => {
        if (document && !isFormInitialized.value) {
            formData.value = {
                title: document.title,
                content: document.content ?? '',
                status: document.status,
                tags: [...(document.tags ?? [])],
            }
            isFormInitialized.value = true
            layoutStore.setPageTitle(`${document.key} | ${document.title}`)
        }
    },
    { immediate: true }
)

useHeaderActions([
    { key: 'save-document', title: 'Save', action: submit, is_primary: true },
    { key: 'cancel-document', title: 'Cancel', action: openView },
])

useBreadcrumbs(() => [
    { label: 'Projects', to: { name: 'projects' } },
    { label: 'Documentation', to: { name: 'project-documentation', params: { projectId } } },
    {
        label: openedDocument.value?.key ?? 'Document',
        to: { name: 'project-documentation.document', params: { projectId, documentId } },
    },
    { label: 'Edit' },
])
</script>

<template>
    <div v-if="openedDocument" class="p-2 flex flex-1 flex-col overflow-hidden">
        <div class="gap-3 p-3 flex flex-col">
            <div class="md:grid-cols-2 gap-3 grid grid-cols-1">
                <InputContainer label="Title" :error="validationErrors.title" required>
                    <InputText
                        v-model="formData.title"
                        placeholder="Document title..."
                        :invalid="!!validationErrors.title"
                    />
                </InputContainer>

                <InputContainer label="Status" :error="validationErrors.status">
                    <Select
                        v-model="formData.status"
                        :options="projectDocumentStatusOptions()"
                        option-label="label"
                        option-value="value"
                        :invalid="!!validationErrors.status"
                    />
                </InputContainer>
            </div>

            <InputContainer label="Tags" :error="validationErrors.tag_ids">
                <div class="gap-2 p-1 flex items-center">
                    <IconButton
                        size="medium"
                        severity="success"
                        icon="mdi:tag-edit"
                        @click="showManageTagsDialog = true"
                    />
                    <TagList :tags="formData.tags" />
                </div>
            </InputContainer>
        </div>

        <div class="flex-1 overflow-auto">
            <MarkdownEditor
                v-model="formData.content"
                preview
                style="height: 100%"
                :handle-image-upload="handleImageUpload"
            />
        </div>

        <ManageRecordTagsDialog v-model:visible="showManageTagsDialog" v-model="formData.tags" />
    </div>
</template>
