<script setup lang="ts">
import { ref } from 'vue'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import { ColorPicker } from 'vue3-colorpicker'
import 'vue3-colorpicker/style.css'
import { InputContainer } from '@/shared/components/input'
import type { ITag } from '@/entities/tag/types'
import { useCreateTagMutation } from '@/entities/tag/mutations'
import { randomHex } from '@/shared/utils/color.util.ts'

const visible = defineModel<boolean>('visible', { required: true })

const emit = defineEmits<{
    created: [tag: ITag]
}>()

const name = ref('')
const color = ref(randomHex())

const { mutate, isPending } = useCreateTagMutation()

function resetForm() {
    name.value = ''
    color.value = randomHex()
}

function onSubmit() {
    if (!name.value.trim()) return

    mutate(
        { name: name.value.trim(), color: color.value },
        {
            onSuccess(response) {
                emit('created', response.data)
                visible.value = false
                resetForm()
            },
        }
    )
}
</script>

<template>
    <Dialog v-model:visible="visible" header="New Tag" modal :closable="!isPending" :style="{ width: '24rem' }">
        <form class="gap-4 pt-1 flex flex-col" @submit.prevent="onSubmit">
            <InputContainer label="Name" required>
                <InputText
                    v-model="name"
                    placeholder="e.g. Bug"
                    :invalid="!name.trim() && name.length > 0"
                    class="w-full"
                />
            </InputContainer>
            <InputContainer label="Color">
                <ColorPicker v-model:pureColor="color" format="hex" />
            </InputContainer>
        </form>

        <template #footer>
            <Button label="Cancel" severity="secondary" text :disabled="isPending" @click="visible = false" />
            <Button label="Create" :loading="isPending" :disabled="!name.trim()" @click="onSubmit" />
        </template>
    </Dialog>
</template>
