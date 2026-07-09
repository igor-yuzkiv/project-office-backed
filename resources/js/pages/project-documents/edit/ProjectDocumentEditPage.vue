<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import { InputContainer } from '@/shared/components/input'
import { MarkdownEditor } from '@/shared/components/md-editor'
import { useProjectDocumentQuery, useUpdateProjectDocumentMutation } from '@/entities/project-document'
import type { IUpdateProjectDocumentInput } from '@/entities/project-document'
import type { ITag } from '@/entities/tag/types'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useHeaderActions, useBreadcrumbs } from '@/app/shell'
import { TagList } from '@/widgets/tags/metadata'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { IconButton } from '@/shared/components/button'

interface ProjectDocumentEditFormData {
    title: string
    content: string
    tags: ITag[]
}

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const documentId = route.params.id as string

const { projectDocument, isError } = useProjectDocumentQuery(documentId)
const { mutate: updateProjectDocument } = useUpdateProjectDocumentMutation()

const formData = ref<ProjectDocumentEditFormData>({
    title: '',
    content: '',
    tags: [],
})
const isFormInitialized = ref(false)
const validationErrors = ref<LaravelValidationErrors>({})
const showManageTagsDialog = ref(false)

function handleError(error: unknown) {
    if (error instanceof ApiError && error.isValidationError) {
        validationErrors.value = error.validationErrors ?? {}
    } else {
        toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to save document.')
    }
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
        tag_ids: formData.value.tags.map((t) => t.id),
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
                tags: doc.tags ?? [],
            }
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
            <div class="p-3">
                <InputContainer label="Title" :error="validationErrors.title" required>
                    <InputText
                        v-model="formData.title"
                        placeholder="Document title..."
                        :invalid="!!validationErrors.title"
                    />
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
                <MarkdownEditor v-model="formData.content" preview style="height: 100%" />
            </div>
        </div>

        <ManageRecordTagsDialog v-model:visible="showManageTagsDialog" v-model="formData.tags" />
    </div>
</template>
