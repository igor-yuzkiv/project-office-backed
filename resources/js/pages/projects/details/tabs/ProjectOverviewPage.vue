<script setup lang="ts">
import { useRoute } from 'vue-router'
import { useProjectQuery } from '@/entities/project/queries'
import { DisplayDate, DisplayField } from '@/shared/components/display'
import { UserAvatar } from '@/widgets/user/user-avatar'

const route = useRoute()
const projectId = route.params.id as string

const { project } = useProjectQuery(projectId)
</script>

<template>
    <div v-if="project" class="gap-4 p-4 grid grid-cols-2">
        <DisplayField label="Name" :value="project.name" />
        <DisplayField label="Prefix" :value="project.prefix" />
        <DisplayField label="Created By">
            <div v-if="project.created_by" class="gap-2 flex items-center">
                <UserAvatar :user="project.created_by" size="small" />
                <span class="text-surface-700">{{ project.created_by.name }}</span>
            </div>
        </DisplayField>
        <DisplayField label="Updated By">
            <div v-if="project.updated_by" class="gap-2 flex items-center">
                <UserAvatar :user="project.updated_by" size="small" />
                <span class="text-surface-700">{{ project.updated_by.name }}</span>
            </div>
        </DisplayField>
        <DisplayDate label="Created At" :date="project.created_at" />
        <DisplayDate label="Updated At" :date="project.updated_at" />
    </div>
</template>
