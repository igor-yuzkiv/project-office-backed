<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import {
    ProjectDocumentAttachmentRoles,
    uploadProjectDocumentAttachmentRequest,
    useProjectDocumentQuery,
    useUpdateProjectDocumentMutation,
} from '@/entities/project-document'
import { projectDocumentStatusOptions } from '@/entities/project-document/config'
import type {
    IProjectDocumentVersion,
    IUpdateProjectDocumentInput,
    ProjectDocumentStatusValue,
} from '@/entities/project-document/types'
import type { ITag } from '@/entities/tag/types'
import {
    DocumentVersionCreateDialog,
    DocumentVersionSwitcher,
    useDocumentVersionEditor,
} from '@/widgets/project-documents/versions'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { TagList } from '@/widgets/tags/metadata'
import { IconButton } from '@/shared/components/button'
import { InputContainer } from '@/shared/components/input'
import { MarkdownEditor } from '@/shared/components/md-editor'
import { ApiError } from '@/shared/api/api.error'
import { useConfirmDialog, useToast } from '@/shared/composables'
import type { LaravelValidationErrors } from '@/shared/types'
import { useBreadcrumbs, useHeaderActions } from '@/app/shell'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'

interface DocumentEditFormData {
    title: string
    status: ProjectDocumentStatusValue
    tags: ITag[]
}

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const confirm = useConfirmDialog()

const projectId = route.params.projectId as string
const documentId = route.params.documentId as string

const { projectDocument, isError } = useProjectDocumentQuery(documentId, { with_path: true })
const { mutate: updateDocument, isPending: isSaving } = useUpdateProjectDocumentMutation()

const openedDocument = computed(() =>
    projectDocument.value?.project_id === projectId ? projectDocument.value : undefined
)

const formData = ref<DocumentEditFormData>({ title: '', status: 'draft', tags: [] })

const versionEditor = useDocumentVersionEditor(documentId)
const showVersionCreateDialog = ref(false)
const isFormInitialized = ref(false)
const validationErrors = ref<LaravelValidationErrors>({})
const showManageTagsDialog = ref(false)

function openView() {
    router.replace({
        name: 'project-documentation.document',
        params: { projectId, documentId },
    })
}

function handleError(error: unknown, fallback = 'Document could not be saved.') {
    if (error instanceof ApiError && error.isValidationError) {
        validationErrors.value = error.validationErrors ?? {}
        toast.error(Object.values(validationErrors.value).flat()[0] ?? fallback)
    } else {
        toast.error(error instanceof ApiError ? error.displayMessage : fallback)
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
        status: formData.value.status,
        tag_ids: formData.value.tags.map((tag) => tag.id),
    }

    updateDocument(
        { id: document.id, data: input },
        {
            // The document's own fields and its versions are two separate writes, and the second
            // one is where the typing lives — so a failure there keeps the drafts and says so
            // rather than leaving the writer to discover it.
            onSuccess: async () => {
                try {
                    await versionEditor.saveDrafts()
                } catch (error) {
                    // The document's own fields are already persisted at this point, so saying the
                    // document could not be saved would send the writer after the wrong thing.
                    handleError(error, 'Document fields were saved, but the version text was not.')

                    return
                }

                toast.success('Document saved.')
                openView()
            },
            onError: (error) => handleError(error),
        }
    )
}

async function createVersion(input: { label: string | null; copyContentFromVersionId: string | null }) {
    try {
        await versionEditor.create({
            label: input.label,
            copy_content_from_version_id: input.copyContentFromVersionId,
        })
        showVersionCreateDialog.value = false
    } catch (error) {
        handleError(error)
    }
}

async function removeVersion(version: IProjectDocumentVersion) {
    const confirmed = await confirm.requireAsync({
        header: 'Delete version',
        message: `Version ${version.version_number} will be deleted permanently, together with anything unsaved in it. This cannot be undone.`,
        acceptLabel: 'Delete',
        rejectLabel: 'Cancel',
    })

    if (!confirmed) return

    try {
        await versionEditor.remove(version)
    } catch (error) {
        handleError(error)
    }
}

async function setPrimary(versionId: string | null) {
    try {
        await versionEditor.setPrimary(versionId)
    } catch (error) {
        handleError(error)
    }
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
//
// Only while the form is still loading. Afterwards the page holds text that exists nowhere else,
// and a background refetch failing is not a reason to navigate out of it.
watch([isError, projectDocument], () => {
    if (isFormInitialized.value) return

    if (isError.value || (projectDocument.value && !openedDocument.value)) openView()
})

watch(
    openedDocument,
    (document) => {
        if (document && !isFormInitialized.value) {
            formData.value = {
                title: document.title,
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

        <div class="gap-2 px-3 pb-2 flex flex-wrap items-center">
            <DocumentVersionSwitcher
                editable
                :versions="versionEditor.versions.value"
                :open-version-id="versionEditor.openVersionId.value"
                :dirty-ids="versionEditor.dirtyIds.value"
                :has-pinned-version="Boolean(openedDocument.primary_version_id)"
                :is-busy="versionEditor.isBusy.value"
                @open="versionEditor.selectVersion"
                @create="showVersionCreateDialog = true"
                @delete="removeVersion"
                @set-primary="setPrimary($event.id)"
                @use-latest-as-primary="setPrimary(null)"
            />

            <span v-if="versionEditor.isDirty.value" class="text-amber-600 dark:text-amber-400 text-xs">
                Unsaved changes
            </span>
        </div>

        <div v-if="versionEditor.openVersion.value" class="flex-1 overflow-auto">
            <MarkdownEditor
                v-model="versionEditor.openContent.value"
                preview
                style="height: 100%"
                :handle-image-upload="handleImageUpload"
            />
        </div>

        <!-- Nothing to type into until a version exists: content belongs to a version, not to
             the document, so the editor has nowhere to put it. Held back while a version write is
             in flight, so it does not flash between creating one and the list catching up. -->
        <div
            v-else-if="!versionEditor.isBusy.value && !versionEditor.isPending.value"
            class="gap-3 p-10 flex flex-1 flex-col items-center justify-center text-center"
        >
            <p class="text-surface-700 dark:text-surface-200 text-sm font-medium">This document has no versions yet</p>
            <p class="text-surface-500 max-w-sm text-xs">Create one to start writing.</p>
            <Button label="New version" size="small" outlined @click="showVersionCreateDialog = true" />
        </div>

        <DocumentVersionCreateDialog
            v-model:visible="showVersionCreateDialog"
            :versions="versionEditor.versions.value"
            :is-pending="versionEditor.isBusy.value"
            @submit="createVersion"
        />

        <ManageRecordTagsDialog v-model:visible="showManageTagsDialog" v-model="formData.tags" />
    </div>
</template>
