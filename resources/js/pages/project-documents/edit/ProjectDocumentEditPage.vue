<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Button from 'primevue/button'
import { Icon } from '@iconify/vue'
import { InputContainer } from '@/shared/components/input'
import { MarkdownEditor } from '@/shared/components/md-editor'
import {
    useProjectDocumentQuery,
    useUpdateProjectDocumentMutation,
    uploadProjectDocumentAttachmentRequest,
    ProjectDocumentAttachmentRoles,
} from '@/entities/project-document'
import type { IUpdateProjectDocumentInput } from '@/entities/project-document'
import { projectDocumentStatusOptions } from '@/entities/project-document/config'
import type { ProjectDocumentPathNodeDto, ProjectDocumentStatusValue } from '@/entities/project-document/types'
import type { ITag } from '@/entities/tag/types'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useHeaderActions, useBreadcrumbs } from '@/app/shell'
import { TagList } from '@/widgets/tags/metadata'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { ProjectDocumentParentPickerDialog } from '@/widgets/project-documents/parent-picker'
import type { ParentPickerSelection } from '@/widgets/project-documents/parent-picker'
import { IconButton } from '@/shared/components/button'

interface ProjectDocumentEditFormData {
    title: string
    content: string
    status: ProjectDocumentStatusValue
    tags: ITag[]
    parentId: string | null
}

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const documentId = route.params.id as string

const { projectDocument, isError } = useProjectDocumentQuery(documentId, { with_path: true })
const { mutate: updateProjectDocument } = useUpdateProjectDocumentMutation()

const formData = ref<ProjectDocumentEditFormData>({
    title: '',
    content: '',
    status: 'draft',
    tags: [],
    parentId: null,
})
const isFormInitialized = ref(false)
const validationErrors = ref<LaravelValidationErrors>({})
const showManageTagsDialog = ref(false)
const showParentPickerDialog = ref(false)
// Parent the document had when loaded — used to detect a staged move and preview it.
const initialParentId = ref<string | null>(null)
const stagedParent = ref<ProjectDocumentPathNodeDto | null>(null)

function handleError(error: unknown) {
    if (error instanceof ApiError && error.isValidationError) {
        validationErrors.value = error.validationErrors ?? {}
    } else {
        toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to save document.')
    }
}

async function handleContentImageUpload(files: File[], callback: (urls: string[]) => void) {
    const results = await Promise.all(
        files.map((file) =>
            uploadProjectDocumentAttachmentRequest(documentId, file, ProjectDocumentAttachmentRoles.CONTENT)
        )
    )
    callback(results.map((res) => res.data.url))
}

function onParentSelected(selection: ParentPickerSelection) {
    formData.value.parentId = selection.parentId
    stagedParent.value = selection.parent
}

function navigateBack() {
    if (window.history.state?.back) {
        router.back()
    } else {
        router.push({ name: 'project-document-details', params: { id: documentId } })
    }
}

function submit() {
    if (!projectDocument.value) return

    validationErrors.value = {}

    const input: IUpdateProjectDocumentInput = {
        title: formData.value.title,
        content: formData.value.content,
        status: formData.value.status,
        tag_ids: formData.value.tags.map((t) => t.id),
        parent_id: formData.value.parentId,
    }

    updateProjectDocument(
        { id: documentId, data: input },
        {
            onSuccess: navigateBack,
            onError: handleError,
        }
    )
}

watch(isError, (error) => {
    if (error) toast.error('Failed to load document.')
})

watch(
    projectDocument,
    (doc) => {
        if (doc && !isFormInitialized.value) {
            formData.value = {
                title: doc.title,
                content: doc.content ?? '',
                status: doc.status,
                tags: doc.tags ?? [],
                parentId: doc.parent_id,
            }
            initialParentId.value = doc.parent_id
            stagedParent.value = doc.path?.find((node) => node.id === doc.parent_id) ?? null
            isFormInitialized.value = true
            layoutStore.setPageTitle(doc.title)
        }
    },
    { immediate: true }
)

useHeaderActions([
    { key: 'save-project-document', title: 'Save', action: submit, is_primary: true },
    { key: 'cancel-project-document', title: 'Cancel', action: navigateBack },
])

useBreadcrumbs(() => [
    ...(projectDocument.value?.project
        ? [
              {
                  label: projectDocument.value.project.name,
                  to: { name: 'project-details', params: { id: projectDocument.value.project.id } },
              },
          ]
        : []),
    ...(projectDocument.value?.project_id
        ? [
              {
                  label: 'Documentation',
                  to: { name: 'project-details.documentation', params: { id: projectDocument.value.project_id } },
              },
          ]
        : []),
    {
        label: projectDocument.value?.title ?? 'Document',
        to: { name: 'project-document-details', params: { id: documentId } },
    },
    { label: 'Edit' },
])
</script>

<template>
    <div v-if="projectDocument" class="p-2 flex flex-1 flex-col overflow-hidden">
        <div class="flex flex-1 flex-col overflow-hidden">
            <div class="md:grid-cols-2 gap-3 p-3 grid grid-cols-1">
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

            <div class="px-3">
                <InputContainer label="Location" :error="validationErrors.parent_id">
                    <div class="gap-2 flex flex-wrap items-center">
                        <nav class="gap-1 text-sm text-surface-600 dark:text-surface-300 flex flex-wrap items-center">
                            <template v-for="(node, index) in projectDocument.path ?? []" :key="node.id">
                                <Icon v-if="index > 0" icon="heroicons:chevron-right" class="text-surface-400" />
                                <span>{{ node.title }}</span>
                            </template>
                        </nav>
                        <Button
                            label="Change parent"
                            size="small"
                            severity="secondary"
                            text
                            @click="showParentPickerDialog = true"
                        >
                            <template #icon>
                                <Icon icon="heroicons:arrows-right-left" class="text-base" />
                            </template>
                        </Button>
                    </div>
                    <div
                        v-if="formData.parentId !== initialParentId"
                        class="text-xs text-amber-600 dark:text-amber-400"
                    >
                        Will move under: <strong>{{ stagedParent?.title ?? 'Root (no parent)' }}</strong>
                    </div>
                </InputContainer>
            </div>

            <div class="px-3">
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
                    :handle-image-upload="handleContentImageUpload"
                />
            </div>
        </div>

        <ManageRecordTagsDialog v-model:visible="showManageTagsDialog" v-model="formData.tags" />

        <ProjectDocumentParentPickerDialog
            v-model:visible="showParentPickerDialog"
            :project-id="projectDocument.project_id"
            :current-document-id="documentId"
            :validation-errors="validationErrors"
            @select="onParentSelected"
        />
    </div>
</template>
