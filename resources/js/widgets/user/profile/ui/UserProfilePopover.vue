<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink } from 'vue-router'
import Popover from 'primevue/popover'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'
import { Icon } from '@iconify/vue'
import { getInitials } from '@/shared/utils/string.util.ts'

const props = defineProps<{
    name: string
    email: string
}>()

const emit = defineEmits<{
    (e: 'logout'): void
}>()

const initials = computed(() => getInitials(props.name))

const popover = ref<InstanceType<typeof Popover>>()

function toggle(event: MouseEvent) {
    popover.value?.toggle(event)
}

function hide() {
    popover.value?.hide()
}

defineExpose({ toggle })
</script>

<template>
    <Popover ref="popover">
        <div class="min-w-52 gap-3 p-1 flex flex-col">
            <div class="gap-2 flex items-center">
                <Avatar :label="initials" shape="circle" size="large" />
                <div class="gap-1 flex flex-col overflow-hidden">
                    <span class="text-sm font-medium text-ink truncate">
                        {{ name }}
                    </span>
                    <span class="text-xs text-ink-2 truncate">
                        {{ email }}
                    </span>
                </div>
            </div>

            <RouterLink
                :to="{ name: 'profile' }"
                class="gap-2 rounded-md px-2 py-1.5 text-sm text-ink-2 hover:bg-hover hover:text-ink flex items-center transition-colors"
                @click="hide"
            >
                <Icon icon="heroicons:user-circle" class="h-4 w-4 shrink-0" />
                <span>Profile</span>
            </RouterLink>

            <Button label="Sign Out" severity="secondary" size="small" fluid @click="emit('logout')" />
        </div>
    </Popover>
</template>
