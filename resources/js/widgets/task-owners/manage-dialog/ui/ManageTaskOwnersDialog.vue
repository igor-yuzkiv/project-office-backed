<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Icon } from '@iconify/vue'
import { refDebounced } from '@vueuse/core'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Select from 'primevue/select'

import { useTaskOwnersQuery } from '@/entities/task-owner/queries'
import { useSyncTaskOwnersMutation } from '@/entities/task-owner/mutations'
import { TASK_OWNER_ROLES, type TaskOwnerDraft } from '@/entities/task-owner/types'
import { useUsersSearchQuery } from '@/entities/user/queries'
import { useToast } from '@/shared/composables/use.toast'

const props = defineProps<{
    taskId: string
}>()

const visible = defineModel<boolean>('visible', { required: true })

const emit = defineEmits<{
    (e: 'saved'): void
}>()

const toast = useToast()

const { owners } = useTaskOwnersQuery(() => props.taskId)
const { mutate: syncOwners, isPending: isSaving } = useSyncTaskOwnersMutation(() => props.taskId)

const draftOwners = ref<TaskOwnerDraft[]>([])

const searchQuery = ref('')
const debouncedSearchQuery = refDebounced(
    computed(() => searchQuery.value.trim()),
    300
)

const { users: searchResults } = useUsersSearchQuery(debouncedSearchQuery)

const assignedUserIds = computed(() => new Set(draftOwners.value.map((o) => o.user_id)))

const availableUsers = computed(() => searchResults.value.filter((u) => !assignedUserIds.value.has(u.id)))

const roleOptions = [{ label: '—', value: null }, ...TASK_OWNER_ROLES.map((r) => ({ label: r, value: r }))]

function initDraft() {
    draftOwners.value = (owners.value ?? []).map((o) => ({
        user_id: o.user.id,
        user_name: o.user.name,
        role: o.role,
        is_primary: o.is_primary,
    }))
}

function setPrimary(index: number) {
    draftOwners.value = draftOwners.value.map((o, i) => ({ ...o, is_primary: i === index }))
}

function removeOwner(index: number) {
    draftOwners.value.splice(index, 1)
}

function addUser(user: { id: string; name: string }) {
    if (assignedUserIds.value.has(user.id)) return
    draftOwners.value.push({ user_id: user.id, user_name: user.name, role: null, is_primary: false })
    searchQuery.value = ''
}

function onCancel() {
    visible.value = false
    initDraft()
}

function onSave() {
    syncOwners(
        {
            owners: draftOwners.value.map(({ user_id, role, is_primary }) => ({ user_id, role, is_primary })),
        },
        {
            onSuccess() {
                emit('saved')
                visible.value = false
            },
            onError() {
                toast.error('Failed to save task owners. Please try again.')
            },
        }
    )
}

watch(
    visible,
    (isVisible) => {
        if (isVisible) {
            initDraft()
            searchQuery.value = ''
        }
    },
    { immediate: false }
)

watch(owners, () => {
    if (!visible.value) return
    initDraft()
})
</script>

<template>
    <Dialog v-model:visible="visible" modal :closable="true" :style="{ width: '38rem' }">
        <template #header>
            <div class="gap-2 flex items-center">
                <i class="pi pi-users text-primary" />
                <span class="text-base font-semibold">Manage Task Owners</span>
            </div>
        </template>

        <div class="gap-5 py-1 flex flex-col">
            <!-- Assigned Owners -->
            <div class="gap-3 flex flex-col">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold tracking-wide text-surface-500 uppercase">
                        Assigned Owners
                    </span>
                    <span class="text-xs text-surface-500">{{ draftOwners.length }} assigned</span>
                </div>

                <div
                    v-if="draftOwners.length > 0"
                    class="divide-surface-200 dark:divide-surface-700 flex flex-col divide-y"
                >
                    <div
                        v-for="(owner, index) in draftOwners"
                        :key="owner.user_id"
                        class="gap-3 py-2 flex items-center"
                    >
                        <button
                            type="button"
                            class="shrink-0 focus:outline-none"
                            :title="owner.is_primary ? 'Primary owner' : 'Set as primary'"
                            @click="setPrimary(index)"
                        >
                            <Icon
                                :icon="owner.is_primary ? 'material-symbols:star' : 'material-symbols:star-outline'"
                                class="text-xl"
                                :class="owner.is_primary ? 'text-yellow-400' : 'text-surface-400'"
                            />
                        </button>

                        <span class="min-w-0 text-sm flex-1 truncate">{{ owner.user_name }}</span>

                        <Select
                            v-model="draftOwners[index].role"
                            :options="roleOptions"
                            option-label="label"
                            option-value="value"
                            placeholder="No role"
                            class="w-40 shrink-0"
                            size="small"
                        />

                        <Button
                            icon="pi pi-times"
                            severity="secondary"
                            text
                            rounded
                            size="small"
                            class="shrink-0"
                            @click="removeOwner(index)"
                        />
                    </div>
                </div>

                <p v-else class="text-sm text-surface-400">No owners assigned yet</p>
            </div>

            <!-- Available Users -->
            <div class="gap-3 flex flex-col">
                <span class="text-xs font-semibold tracking-wide text-surface-500 uppercase">Available Users</span>

                <InputText v-model="searchQuery" placeholder="Type to search users..." class="w-full" />

                <div class="max-h-52 divide-surface-200 dark:divide-surface-700 flex flex-col divide-y overflow-y-auto">
                    <template v-if="debouncedSearchQuery.length >= 1">
                        <div
                            v-for="user in availableUsers"
                            :key="user.id"
                            class="gap-3 hover:bg-surface-50 dark:hover:bg-surface-800 px-1 py-2 flex cursor-pointer items-center"
                            @click="addUser(user)"
                        >
                            <i class="pi pi-user text-surface-400" />
                            <span class="text-sm">{{ user.name }}</span>
                        </div>

                        <div v-if="availableUsers.length === 0" class="py-3 text-sm text-surface-400 text-center">
                            No matching users
                        </div>
                    </template>

                    <div v-else class="py-3 text-sm text-surface-400 text-center">Type to search users</div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="gap-2 flex justify-end">
                <Button label="Cancel" severity="secondary" outlined @click="onCancel" />
                <Button label="Save" :loading="isSaving" @click="onSave" />
            </div>
        </template>
    </Dialog>
</template>
