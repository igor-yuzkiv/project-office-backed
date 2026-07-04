<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import DatePicker from 'primevue/datepicker'
import Button from 'primevue/button'
import Message from 'primevue/message'
import { InputContainer } from '@/shared/components/input'
import { CopyToClipboard } from '@/shared/components/display'
import { useCreateApiTokenMutation } from '@/entities/user/mutations'
import { ApiError } from '@/shared/api'
import type { LaravelValidationErrors } from '@/shared/types'

const visible = defineModel<boolean>('visible', { required: true })

type Step = 'form' | 'created'

const step = ref<Step>('form')
const name = ref('')
const expiresAt = ref<Date | null>(null)
const validationErrors = ref<LaravelValidationErrors>({})
const createdPlainTextToken = ref<string | null>(null)

const today = computed(() => {
    const date = new Date()
    date.setHours(0, 0, 0, 0)
    return date
})

const { mutate: createToken, isPending: isCreating } = useCreateApiTokenMutation()

function defaultExpiresAt(): Date {
    const date = new Date()
    date.setDate(date.getDate() + 30)
    return date
}

function resetForm() {
    step.value = 'form'
    name.value = ''
    expiresAt.value = defaultExpiresAt()
    validationErrors.value = {}
    createdPlainTextToken.value = null
}

function formatDateForApi(date: Date): string {
    return date.toISOString().split('T')[0]
}

function submit() {
    if (!expiresAt.value) return

    validationErrors.value = {}

    createToken(
        { name: name.value, expires_at: formatDateForApi(expiresAt.value) },
        {
            onSuccess: (result) => {
                createdPlainTextToken.value = result.plain_text_token
                step.value = 'created'
            },
            onError: (error) => {
                if (error instanceof ApiError && error.isValidationError) {
                    validationErrors.value = error.validationErrors ?? {}
                }
            },
        }
    )
}

function close() {
    visible.value = false
}

watch(visible, (isVisible) => {
    if (isVisible) resetForm()
})
</script>

<template>
    <Dialog v-model:visible="visible" modal :closable="true" header="Create API Token" :style="{ width: '30rem' }">
        <div v-if="step === 'form'" class="gap-4 flex flex-col">
            <InputContainer label="Name" :error="validationErrors.name" required>
                <InputText
                    v-model="name"
                    placeholder="e.g. project-office-cli"
                    :invalid="!!validationErrors.name"
                    fluid
                />
            </InputContainer>

            <InputContainer label="Expires At" :error="validationErrors.expires_at" required>
                <DatePicker
                    v-model="expiresAt"
                    date-format="yy-mm-dd"
                    :min-date="today"
                    :invalid="!!validationErrors.expires_at"
                    fluid
                />
            </InputContainer>
        </div>

        <div v-else class="gap-4 flex flex-col">
            <Message severity="warn" :closable="false">
                Copy this token now — it won't be shown again after you close this dialog.
            </Message>

            <div class="bg-surface-100 dark:bg-surface-800 rounded p-3 break-all">
                <CopyToClipboard :text="createdPlainTextToken" />
            </div>
        </div>

        <template #footer>
            <div v-if="step === 'form'" class="gap-2 flex justify-end">
                <Button label="Cancel" severity="secondary" outlined @click="close" />
                <Button label="Create" :loading="isCreating" @click="submit" />
            </div>
            <div v-else class="gap-2 flex justify-end">
                <Button label="Done" @click="close" />
            </div>
        </template>
    </Dialog>
</template>
