<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { onBeforeRouteLeave, onBeforeRouteUpdate, useRouter } from 'vue-router'
import { useRouteParams } from '@vueuse/router'
import { useEventListener } from '@vueuse/core'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { projectDocumentStatusOptions } from '@/entities/project-document/config'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { TagList } from '@/widgets/tags/metadata'
import { IconButton } from '@/shared/components/button'
import { InputContainer } from '@/shared/components/input'
import { MarkdownEditor } from '@/shared/components/md-editor'
import { useBreadcrumbs, useHeaderActions } from '@/app/shell'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useDocumentEditing } from './composables/use.document-editing'

const router = useRouter()
const layoutStore = useAppLayoutStore()

const projectId = useRouteParams<string>('projectId')
const documentId = useRouteParams<string>('documentId')

const { projectDocument, isError } = useProjectDocumentQuery(documentId, { with_path: true })

const openedDocument = computed(() =>
    projectDocument.value?.project_id === projectId.value ? projectDocument.value : undefined
)

const showManageTagsDialog = ref(false)

// The draft belongs to this page and dies with it, which is what an explicit save
// means. The guards below are the only thing standing between the two.
const editing = useDocumentEditing(openedDocument, {
    onSaved: () => openView(),
})

function openView() {
    router.replace({
        name: 'project-documentation.document',
        params: { projectId: projectId.value, documentId: documentId.value },
    })
}

// Not found, wrong project, failed request — the workspace already says all three,
// so the editor hands those cases back instead of showing an empty form. The draft is
// dropped first: there is nothing left to ask the user about saving it into.
watch([isError, projectDocument], () => {
    if (!isError.value && !(projectDocument.value && !openedDocument.value)) return

    editing.cancel()
    openView()
})

// Entering the route is what starts editing, and it starts exactly once: after a save
// the document refetches, and a plain watch would re-fill the draft while the
// navigation away is still in flight.
const hasStarted = ref(false)

watch(
    openedDocument,
    (document) => {
        if (!document || hasStarted.value) return

        hasStarted.value = true
        editing.start()
        layoutStore.setPageTitle(`${document.key} | ${document.title}`)
    },
    { immediate: true }
)

async function cancel() {
    if (await editing.confirmDiscard()) openView()
}

onBeforeRouteLeave(() => editing.confirmDiscard())

// This route is its own record, so moving from one document's editor straight to
// another's reuses the component: the draft has to be asked about and then rebuilt,
// or document A's text would be saved over document B.
onBeforeRouteUpdate(async (to, from) => {
    if (to.params.documentId === from.params.documentId) return true

    if (!(await editing.confirmDiscard())) return false

    hasStarted.value = false

    return true
})

useEventListener(window, 'beforeunload', (event: BeforeUnloadEvent) => {
    if (!editing.isDirty.value) return

    event.preventDefault()
    event.returnValue = ''
})

useHeaderActions([
    { key: 'save-document', title: 'Save', action: () => editing.save(), is_primary: true },
    { key: 'cancel-document', title: 'Cancel', action: cancel },
])

useBreadcrumbs(() => [
    { label: 'Projects', to: { name: 'projects' } },
    { label: 'Documentation', to: { name: 'project-documentation', params: { projectId: projectId.value } } },
    {
        label: openedDocument.value?.key ?? 'Document',
        to: {
            name: 'project-documentation.document',
            params: { projectId: projectId.value, documentId: documentId.value },
        },
    },
    { label: 'Edit' },
])
</script>

<template>
    <div v-if="openedDocument" class="p-2 flex flex-1 flex-col overflow-hidden">
        <div class="gap-3 p-3 flex flex-col">
            <div class="md:grid-cols-2 gap-3 grid grid-cols-1">
                <InputContainer label="Title" :error="editing.validationErrors.value.title" required>
                    <InputText
                        v-model="editing.draft.value.title"
                        placeholder="Document title..."
                        :invalid="!!editing.validationErrors.value.title"
                    />
                </InputContainer>

                <InputContainer label="Status" :error="editing.validationErrors.value.status">
                    <Select
                        v-model="editing.draft.value.status"
                        :options="projectDocumentStatusOptions()"
                        option-label="label"
                        option-value="value"
                        :invalid="!!editing.validationErrors.value.status"
                    />
                </InputContainer>
            </div>

            <InputContainer label="Tags" :error="editing.validationErrors.value.tag_ids">
                <div class="gap-2 p-1 flex items-center">
                    <IconButton
                        size="medium"
                        severity="success"
                        icon="mdi:tag-edit"
                        @click="showManageTagsDialog = true"
                    />
                    <TagList :tags="editing.draft.value.tags" />
                </div>
            </InputContainer>
        </div>

        <div class="flex-1 overflow-auto">
            <MarkdownEditor
                v-model="editing.draft.value.content"
                preview
                style="height: 100%"
                :handle-image-upload="editing.handleContentImageUpload"
            />
        </div>

        <ManageRecordTagsDialog v-model:visible="showManageTagsDialog" v-model="editing.draft.value.tags" />
    </div>
</template>
