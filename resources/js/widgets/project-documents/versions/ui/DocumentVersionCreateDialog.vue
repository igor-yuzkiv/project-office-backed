<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import type { IProjectDocumentVersion } from '@/entities/project-document/types'
import { InputContainer } from '@/shared/components/input'

const visible = defineModel<boolean>('visible', { default: false })

const props = defineProps<{
    versions: IProjectDocumentVersion[]
    isPending: boolean
}>()

const emit = defineEmits<{
    (e: 'submit', input: { label: string | null; copyContentFromVersionId: string | null }): void
}>()

const label = ref('')
const copyFromId = ref<string | null>(null)

const options = computed(() =>
    props.versions.map((version) => ({
        id: version.id,
        label: version.label ? `v${version.version_number} — ${version.label}` : `v${version.version_number}`,
    }))
)

// Copying the newest is what a writer almost always wants; starting empty is a deliberate choice
// they make by clearing the field.
watch(visible, (open) => {
    if (!open) return

    label.value = ''
    copyFromId.value = props.versions.at(-1)?.id ?? null
})

function submit() {
    emit('submit', { label: label.value.trim() || null, copyContentFromVersionId: copyFromId.value })
}
</script>

<template>
    <Dialog v-model:visible="visible" header="New version" modal :closable="!isPending" :style="{ width: '26rem' }">
        <form class="gap-4 pt-1 flex flex-col" @submit.prevent="submit">
            <InputContainer label="Name">
                <InputText v-model="label" placeholder="Optional, e.g. Stable" class="w-full" />
            </InputContainer>

            <InputContainer label="Copy content from">
                <Select
                    v-model="copyFromId"
                    :options="options"
                    option-label="label"
                    option-value="id"
                    placeholder="Start empty"
                    show-clear
                    class="w-full"
                />
            </InputContainer>
        </form>

        <template #footer>
            <Button label="Cancel" severity="secondary" text :disabled="isPending" @click="visible = false" />
            <Button label="Create" :loading="isPending" @click="submit" />
        </template>
    </Dialog>
</template>
