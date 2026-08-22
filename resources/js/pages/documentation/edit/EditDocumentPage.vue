<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { onBeforeRouteLeave, onBeforeRouteUpdate, useRouter } from 'vue-router'
import { useRouteParams } from '@vueuse/router'
import { useEventListener } from '@vueuse/core'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Skeleton from 'primevue/skeleton'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { projectDocumentStatusOptions } from '@/entities/project-document/config'
import { useDocumentEditing } from './composables/use.document-editing'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { TagList } from '@/widgets/tags/metadata'
import { IconButton } from '@/shared/components/button'
import { MarkdownEditor } from '@/shared/components/md-editor'
import { useBreadcrumbs } from '@/app/shell'

const router = useRouter()

const projectId = useRouteParams<string>('projectId')
const documentId = useRouteParams<string>('documentId')

const { projectDocument, isError, isFetching } = useProjectDocumentQuery(documentId, { with_path: true })

const openedDocument = computed(() =>
    projectDocument.value?.project_id === projectId.value ? projectDocument.value : undefined
)

const isTagsDialogVisible = ref(false)

// The draft belongs to this page and dies with it, which is what an explicit save
// means. The guard below is the only thing standing between the two.
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

useBreadcrumbs(() => [
    { label: 'Projects', to: { name: 'projects' } },
    {
        label: 'Documentation',
        to: { name: 'project-documentation', params: { projectId: projectId.value } },
    },
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
    <div class="gap-2 p-2 flex flex-1 overflow-hidden">
        <div
            class="border-surface-200 dark:border-surface-700 bg-surface-0 dark:bg-surface-900 gap-3 p-4 rounded-xl flex flex-1 flex-col overflow-hidden border"
        >
            <div v-if="!openedDocument && isFetching" class="gap-3 flex flex-col">
                <Skeleton height="2rem" width="20rem" />
                <Skeleton v-for="n in 6" :key="n" height="1rem" />
            </div>

            <template v-else-if="openedDocument">
                <div class="gap-2 flex shrink-0 items-center">
                    <span class="text-surface-400 text-sm">{{ openedDocument.key }}</span>

                    <div class="gap-1 ml-auto flex items-center">
                        <span v-if="editing.isDirty.value" class="mr-2 text-xs text-amber-600 dark:text-amber-400">
                            Unsaved changes
                        </span>

                        <Button label="Cancel" size="small" text severity="secondary" @click="cancel">
                            <template #icon><Icon icon="heroicons:x-mark" class="mr-1 text-base" /></template>
                        </Button>

                        <Button
                            :label="editing.isSaving.value ? 'Saving…' : 'Save'"
                            size="small"
                            :disabled="editing.isSaving.value"
                            @click="editing.save"
                        >
                            <template #icon><Icon icon="heroicons:check" class="mr-1 text-base" /></template>
                        </Button>
                    </div>
                </div>

                <div class="gap-3 md:grid-cols-[1fr_12rem] grid shrink-0 grid-cols-1">
                    <label class="gap-1 flex flex-col">
                        <span class="text-surface-500 text-xs">Title</span>
                        <InputText v-model="editing.draft.value.title" size="small" />
                    </label>

                    <label class="gap-1 flex flex-col">
                        <span class="text-surface-500 text-xs">Status</span>
                        <Select
                            v-model="editing.draft.value.status"
                            :options="projectDocumentStatusOptions()"
                            option-label="label"
                            option-value="value"
                            size="small"
                        />
                    </label>
                </div>

                <div class="gap-2 flex shrink-0 items-center">
                    <span class="text-surface-500 text-xs">Tags</span>
                    <IconButton
                        size="small"
                        severity="success"
                        icon="mdi:tag-edit"
                        @click="isTagsDialogVisible = true"
                    />
                    <TagList :tags="editing.draft.value.tags" />
                </div>

                <MarkdownEditor
                    v-model="editing.draft.value.content"
                    preview
                    class="min-h-0 flex-1"
                    style="height: 100%"
                    :handle-image-upload="editing.handleContentImageUpload"
                />
            </template>
        </div>

        <ManageRecordTagsDialog v-model:visible="isTagsDialogVisible" v-model="editing.draft.value.tags" />
    </div>
</template>
