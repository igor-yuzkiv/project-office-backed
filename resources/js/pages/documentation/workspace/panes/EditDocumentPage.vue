<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { onBeforeRouteLeave, useRoute, useRouter } from 'vue-router'
import { useRouteParams, useRouteQuery } from '@vueuse/router'
import { useEventListener } from '@vueuse/core'
import Skeleton from 'primevue/skeleton'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { DocumentBody } from '@/widgets/project-documents/document-body'
import { DocumentDetailsPanel } from '@/widgets/project-documents/document-details'
import { useProjectDocumentEditing } from '@/widgets/project-documents/document-editing'

const router = useRouter()
const route = useRoute()

const emit = defineEmits<{
    (e: 'saved'): void
}>()

const projectId = useRouteParams<string>('projectId')
const documentId = useRouteParams<string>('documentId')

const isDetailsOpen = useRouteQuery<string>('details', '1')

const { projectDocument, isError, isFetching } = useProjectDocumentQuery(documentId, { with_path: true })

const openedDocument = computed(() =>
    projectDocument.value?.project_id === projectId.value ? projectDocument.value : undefined
)

// Not found, wrong project, failed request — the view route already says all three,
// so the editor hands those cases back instead of showing an empty column.
watch([isError, projectDocument], () => {
    if (isError.value || (projectDocument.value && !openedDocument.value)) openView()
})

// The draft belongs to this page and dies with it, which is what an explicit save
// means. The guard below is the only thing standing between the two.
// The tree keeps its own row snapshots, so a renamed document reaches it only if its
// owner is told; the layout listens for this.
const editing = useProjectDocumentEditing(openedDocument, {
    onSaved: () => {
        emit('saved')
        openView()
    },
})

const isPanelOpen = computed({
    get: () => isDetailsOpen.value !== '0',
    set: (open: boolean) => {
        isDetailsOpen.value = open ? '1' : '0'
    },
})

function openView() {
    router.replace({
        name: 'project-documentation.document',
        params: { projectId: projectId.value, documentId: documentId.value },
        query: route.query,
    })
}

onBeforeRouteLeave(() => editing.confirmDiscard())

useEventListener(window, 'beforeunload', (event: BeforeUnloadEvent) => {
    if (!editing.isDirty.value) return

    event.preventDefault()
    event.returnValue = ''
})

// Entering the route is what starts editing, and it starts exactly once: after a
// save the document refetches, and a plain watch would re-enter the editor while
// the navigation away is still in flight.
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

// The toolbar lives in the layout and the draft lives here, so the two meet through
// this contract and nothing else.
// Leaving without being asked: the document is gone, so there is nothing to go back to.
function abandon() {
    editing.cancel()
}

defineExpose({
    save: editing.save,
    cancel,
    abandon,
    isDirty: editing.isDirty,
    isSaving: editing.isSaving,
})
</script>

<template>
    <div class="min-h-0 flex flex-1">
        <section class="min-w-0 flex flex-1 flex-col overflow-auto">
            <div v-if="!openedDocument && isFetching" class="gap-3 p-8 flex flex-col">
                <Skeleton height="2rem" width="20rem" />
                <Skeleton v-for="n in 6" :key="n" height="1rem" />
            </div>

            <DocumentBody
                v-else-if="openedDocument"
                v-model:draft-title="editing.draft.value.title"
                v-model:draft-content="editing.draft.value.content"
                :document="openedDocument"
                is-editing
                :handle-image-upload="editing.handleContentImageUpload"
            />
        </section>

        <div
            class="border-surface-200 dark:border-surface-700 shrink-0 overflow-hidden border-l"
            :class="isPanelOpen ? 'w-96' : 'w-12'"
        >
            <DocumentDetailsPanel
                v-model:open="isPanelOpen"
                v-model:draft-status="editing.draft.value.status"
                v-model:draft-tags="editing.draft.value.tags"
                :document="openedDocument"
                is-editing
            />
        </div>
    </div>
</template>
