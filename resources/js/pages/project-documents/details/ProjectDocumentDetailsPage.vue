<script setup lang="ts">
import { computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useRouteParams } from '@vueuse/router'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import Tabs from 'primevue/tabs'
import { useProjectDocumentQuery, useDeleteProjectDocumentMutation } from '@/entities/project-document'
import { DisplayField, CopyToClipboard } from '@/shared/components/display'
import { ProjectDocumentStatusTag } from '@/widgets/project-documents/status-tag'
import { ProjectIcon } from '@/widgets/projects/project-icon'
import { ProjectDocumentMoveDialog, useProjectDocumentMove } from '@/widgets/project-documents/move-dialog'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useHeaderActions, useBreadcrumbs } from '@/app/shell'
import { TagList } from '@/widgets/tags/metadata'

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const documentId = useRouteParams<string>('id')

const { projectDocument, isError } = useProjectDocumentQuery(documentId, { with_path: true })
const { mutateWithConfirm: deleteProjectDocument } = useDeleteProjectDocumentMutation()
const moveDialog = useProjectDocumentMove(() => documentId.value)

function handleDeleteProjectDocument() {
    if (!projectDocument.value) return

    const projectId = projectDocument.value.project_id
    deleteProjectDocument(
        projectDocument.value.id,
        `Are you sure you want to delete "${projectDocument.value.title}"? This will also delete all nested documents, comments, attachments, tags, and task links.`,
        () => router.push({ name: 'project-details.documentation', params: { id: projectId } })
    )
}

const activeTab = computed(
    () =>
        String(route.name ?? '')
            .split('.')
            .at(-1) ?? 'details'
)

watch(isError, (error) => {
    if (error) toast.error('Failed to load document.')
})

watch(
    projectDocument,
    (doc) => {
        if (doc) layoutStore.setPageTitle(`${doc.key} | ${doc.title}`)
    },
    { immediate: true }
)

function onTabChange(value: string | number) {
    router.push({ name: `project-document-details.${value}`, params: { id: documentId.value } })
}

useHeaderActions(() => [
    {
        key: 'edit-project-document',
        title: 'Edit',
        to: { name: 'project-document-edit', params: { id: documentId.value } },
        is_primary: true,
    },
    { key: 'move-project-document', title: 'Move', action: moveDialog.open },
    { key: 'delete-project-document', title: 'Delete', action: handleDeleteProjectDocument },
])

useBreadcrumbs(() => [
    ...(projectDocument.value?.project
        ? [
              {
                  label: projectDocument.value.project.name,
                  to: { name: 'project-details', params: { id: projectDocument.value.project_id } },
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
    { label: projectDocument.value ? projectDocument.value.key : 'Document' },
])
</script>

<template>
    <div v-if="projectDocument" class="p-2 flex flex-1 overflow-hidden">
        <Tabs :value="activeTab" class="flex flex-1 flex-col overflow-hidden" @update:value="onTabChange">
            <div class="p-3 flex shrink-0 items-start justify-between truncate">
                <div class="gap-1 flex flex-col truncate">
                    <div class="gap-x-3 flex items-center">
                        <DisplayField v-if="projectDocument.project" inline>
                            <ProjectIcon
                                :prefix="projectDocument.project.prefix"
                                size="small"
                                :status="projectDocument.project.status"
                            />
                            <RouterLink
                                :to="{ name: 'project-details', params: { id: projectDocument.project_id } }"
                                class="text-sm app-link"
                            >
                                {{ projectDocument.project.name }}
                            </RouterLink>
                        </DisplayField>
                    </div>

                    <div class="gap-x-2 text-2xl font-semibold flex items-center truncate">
                        <CopyToClipboard class="text-surface-400" :text="projectDocument.key" hide-copy-icon />
                        <h1 class="text-surface-900 dark:text-surface-0 truncate">{{ projectDocument.title }}</h1>
                    </div>

                    <TagList :tags="projectDocument.tags ?? []" />

                    <div
                        v-if="projectDocument.path && projectDocument.path.length > 1"
                        class="gap-1 text-xs text-surface-500 flex items-center truncate"
                    >
                        <template v-for="(node, index) in projectDocument.path" :key="node.id">
                            <span v-if="index > 0" class="text-surface-400">/</span>
                            <RouterLink
                                v-if="node.id !== projectDocument.id"
                                :to="{ name: 'project-document-details', params: { id: node.id } }"
                                class="app-link"
                            >
                                {{ node.title }}
                            </RouterLink>
                            <span v-else>{{ node.title }}</span>
                        </template>
                    </div>
                </div>

                <div class="gap-2 flex items-center">
                    <ProjectDocumentStatusTag :status="projectDocument.status" class="w-fit" />
                </div>
            </div>

            <TabList>
                <Tab value="details" class="px-4 py-2">Details</Tab>
                <Tab value="content" class="px-4 py-2">Content</Tab>
                <Tab value="children" class="px-4 py-2">Child Documents</Tab>
                <Tab value="related-tasks" class="px-4 py-2">Related Tasks</Tab>
                <Tab value="comments" class="px-4 py-2">Comments</Tab>
            </TabList>

            <div class="min-h-0 flex-1 overflow-auto">
                <router-view v-slot="{ Component }">
                    <transition name="page" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </router-view>
            </div>
        </Tabs>

        <ProjectDocumentMoveDialog
            v-model:visible="moveDialog.visible.value"
            :project-id="projectDocument.project_id"
            :current-document-id="projectDocument.id"
            :validation-errors="moveDialog.validationErrors.value"
            @select="moveDialog.handleSelect"
        />
    </div>
</template>
