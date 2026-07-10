<script setup lang="ts">
import { ref } from 'vue'
import Panel from 'primevue/panel'
import InputText from 'primevue/inputtext'
import ToggleSwitch from 'primevue/toggleswitch'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { InputContainer } from '@/shared/components/input'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { UploadAttachmentButton } from '@/widgets/attachments/attachment-uploader'
import { ApiTokensTable, CreateApiTokenDialog } from '@/widgets/user/api-tokens'
import { useApiTokensQuery } from '@/entities/user/queries'
import { useUploadUserAvatarMutation } from '@/entities/user/mutations'
import { useToast } from '@/shared/composables/use.toast'

const authStore = useAuthStore()
const toast = useToast()

const { tokens: apiTokens, isPending: isApiTokensPending } = useApiTokensQuery()
const isCreateTokenDialogVisible = ref(false)

const { mutate: uploadAvatar, isPending: isUploadingAvatar } = useUploadUserAvatarMutation()

function handleAvatarSelected(file: File) {
    uploadAvatar(file, {
        onError() {
            toast.error('Failed to upload avatar. Please try again.')
        },
    })
}
</script>

<template>
    <div v-if="authStore.user" class="md:col-span-2 gap-4 p-2 flex flex-col overflow-auto">
        <Panel header="Profile Photo" :toggleable="true">
            <p class="text-sm text-surface-500 mb-4">
                Your photo will be visible to your teammates across the platform.
            </p>
            <div class="gap-3 flex items-center">
                <UserAvatar :initials="authStore.user.initials" :avatar-url="authStore.user.avatar_url" size="xlarge" />
                <UploadAttachmentButton :is-uploading="isUploadingAvatar" @file-selected="handleAvatarSelected" />
            </div>
            <p class="text-xs text-surface-400 mt-3">JPG, PNG or GIF. Maximum 5 MB. Recommended 256×256px.</p>
        </Panel>

        <Panel header="Personal Information" :toggleable="true">
            <p class="text-sm text-surface-500 mb-4">
                This information is displayed across the platform. Use your real name for better collaboration.
            </p>
            <div class="gap-4 md:grid-cols-2 grid grid-cols-1">
                <InputContainer label="Full Name" required>
                    <InputText :model-value="authStore.user.name" fluid />
                </InputContainer>
                <InputContainer label="Display Name">
                    <InputText fluid placeholder="How your name appears in comments and mentions" />
                </InputContainer>
                <InputContainer label="Email Address" required class="md:col-span-2">
                    <InputText :model-value="authStore.user.email" fluid />
                </InputContainer>
                <InputContainer label="Job Title">
                    <InputText fluid placeholder="e.g. Product Manager" />
                </InputContainer>
                <InputContainer label="Department">
                    <InputText fluid placeholder="e.g. Product" />
                </InputContainer>
            </div>
        </Panel>

        <Panel header="Notifications" :toggleable="true">
            <p class="text-sm text-surface-500 mb-4">Choose which events you want to be notified about by email.</p>
            <div class="flex flex-col">
                <div class="border-b-gray-100 gap-3 py-3 flex items-center justify-between border-b">
                    <div>
                        <p class="text-sm font-medium text-surface-900 dark:text-surface-0">Task assigned to me</p>
                        <p class="text-xs text-surface-400">When a new task is assigned to your account</p>
                    </div>
                    <ToggleSwitch />
                </div>
                <div class="border-b-gray-100 gap-3 py-3 flex items-center justify-between border-b">
                    <div>
                        <p class="text-sm font-medium text-surface-900 dark:text-surface-0">Mentions & comments</p>
                        <p class="text-xs text-surface-400">When someone mentions you or replies to your comment</p>
                    </div>
                    <ToggleSwitch />
                </div>
                <div class="border-b-gray-100 gap-3 py-3 flex items-center justify-between border-b">
                    <div>
                        <p class="text-sm font-medium text-surface-900 dark:text-surface-0">Due date reminders</p>
                        <p class="text-xs text-surface-400">24 hours before a task I own is due</p>
                    </div>
                    <ToggleSwitch />
                </div>
                <div class="border-b-gray-100 gap-3 py-3 flex items-center justify-between border-b">
                    <div>
                        <p class="text-sm font-medium text-surface-900 dark:text-surface-0">Sprint updates</p>
                        <p class="text-xs text-surface-400">When a sprint starts, ends, or is modified</p>
                    </div>
                    <ToggleSwitch />
                </div>
                <div class="gap-3 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-surface-900 dark:text-surface-0">Weekly digest</p>
                        <p class="text-xs text-surface-400">Summary of activity across your projects every Monday</p>
                    </div>
                    <ToggleSwitch />
                </div>
            </div>
        </Panel>

        <Panel :toggleable="true">
            <template #header>
                <div class="flex flex-1 items-center justify-between">
                    <span>API Tokens</span>
                    <Button label="Create" size="small" @click="isCreateTokenDialogVisible = true" />
                </div>
            </template>

            <ApiTokensTable :tokens="apiTokens" :is-pending="isApiTokensPending" />
        </Panel>

        <CreateApiTokenDialog v-model:visible="isCreateTokenDialogVisible" />
    </div>
</template>
