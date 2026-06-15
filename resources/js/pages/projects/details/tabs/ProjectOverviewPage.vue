<script setup lang="ts">
import { useRoute } from 'vue-router'
import { useProjectQuery } from '@/entities/project/queries'
import { DisplayDate, DisplayField } from '@/shared/components/display'
import { MarkdownPreview } from '@/shared/components/md-editor'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { ProjectStatusTag } from '@/widgets/projects/status-tag'
import { TagList } from '@/widgets/tags/metadata'

const route = useRoute()
const projectId = route.params.id as string

const { project } = useProjectQuery(projectId)
</script>

<template>
    <div v-if="project" class="gap-4 flex flex-col">
        <div class="app-content-background gap-4 p-4 grid grid-cols-2">
            <DisplayField label="Name" :value="project.name" />
            <DisplayField label="Prefix" :value="project.prefix" />
            <DisplayField label="Status">
                <ProjectStatusTag :status="project.status" class="w-fit" />
            </DisplayField>
            <DisplayDate label="Start Date" :date="project.start_date ?? undefined" />
            <DisplayDate label="End Date" :date="project.end_date ?? undefined" />
            <DisplayField label="Created By">
                <div v-if="project?.created_by?.name" class="gap-2 flex items-center">
                    <UserAvatar :user-name="project.created_by.name" size="small" />
                    <span class="text-surface-700 dark:text-surface-300">{{ project.created_by.name }}</span>
                </div>
            </DisplayField>
            <DisplayField label="Updated By">
                <div v-if="project.updated_by?.name" class="gap-2 flex items-center">
                    <UserAvatar :user-name="project.updated_by?.name" size="small" />
                    <span class="text-surface-700 dark:text-surface-300">{{ project.updated_by.name }}</span>
                </div>
            </DisplayField>
            <DisplayDate label="Created At" :date="project.created_at" />
            <DisplayDate label="Updated At" :date="project.updated_at" />
            <DisplayDate v-if="project.archived_at" label="Archived At" :date="project.archived_at" />
            <DisplayField v-if="project.archived_by?.name" label="Archived By">
                <div class="gap-2 flex items-center">
                    <UserAvatar :user-name="project.archived_by.name" size="small" />
                    <span class="text-surface-700 dark:text-surface-300">{{ project.archived_by.name }}</span>
                </div>
            </DisplayField>
            <DisplayField label="Tags" class="col-span-2">
                <TagList :tags="project.tags ?? []" />
            </DisplayField>
        </div>

        <div class="app-content-background p-4">
            <MarkdownPreview v-if="project.description" :model-value="project.description" />
            <p v-else class="text-sm text-surface-400 italic">No description available.</p>
        </div>
    </div>
</template>
