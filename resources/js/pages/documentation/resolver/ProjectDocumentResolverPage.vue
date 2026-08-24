<script setup lang="ts">
import { watch } from 'vue'
import { useRouter } from 'vue-router'
import { useRouteParams } from '@vueuse/router'
import ProgressSpinner from 'primevue/progressspinner'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { useToast } from '@/shared/composables'

const router = useRouter()
const toast = useToast()

const documentId = useRouteParams<string>('id')

// This route renders nothing of its own. It exists for links that know a document
// but not its project — saved URLs and the activity stream — and hands them to the
// workspace, which needs both.
// `with_path` matches the key the workspace uses, so it lands on a warm cache entry
// instead of fetching the same document again. A missing document stays missing, so
// there is nothing to retry — retrying only holds the user on the spinner.
const { projectDocument, isError } = useProjectDocumentQuery(documentId, { with_path: true }, { retry: false })

watch(
    projectDocument,
    (document) => {
        if (!document) return

        router.replace({
            name: 'project-documentation.document',
            params: { projectId: document.project_id, documentId: document.id },
        })
    },
    { immediate: true }
)

// Immediate, like the success watch: an id that already failed within the cache
// window mounts in the error state, and a watcher without it would never fire.
watch(
    isError,
    (failed) => {
        if (!failed) return

        toast.error('Could not open that document.')
        router.replace({ name: 'projects' })
    },
    { immediate: true }
)
</script>

<template>
    <div class="gap-3 p-10 flex flex-1 flex-col items-center justify-center">
        <ProgressSpinner style="width: 2.5rem; height: 2.5rem" />
        <p class="text-surface-500 text-sm">Opening the document…</p>
    </div>
</template>
