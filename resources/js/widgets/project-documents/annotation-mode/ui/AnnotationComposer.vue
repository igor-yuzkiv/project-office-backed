<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue'
import Button from 'primevue/button'
import Textarea from 'primevue/textarea'

const props = defineProps<{
    blockLabel: string
    isEditing: boolean
    isSaving: boolean
}>()

const emit = defineEmits<{
    (e: 'save'): void
    (e: 'cancel'): void
}>()

const draft = defineModel<string>('draft', { required: true })

const textareaRef = ref<{ $el?: HTMLTextAreaElement }>()

const canSave = computed(() => draft.value.trim().length > 0 && !props.isSaving)

function focus() {
    nextTick(() => textareaRef.value?.$el?.focus())
}

/** Enter sends, Shift+Enter breaks the line — the shape everyone already knows from chat. */
function handleKeydown(event: KeyboardEvent) {
    if (event.key !== 'Enter' || event.shiftKey) return

    event.preventDefault()

    if (canSave.value) emit('save')
}

watch(() => props.blockLabel, focus, { immediate: true })

defineExpose({ focus })
</script>

<template>
    <div class="p-4 border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-900 shrink-0 border-t">
        <!-- The actions live inside the field, so the bar stays one object rather than a form. -->
        <div class="max-w-5xl relative mx-auto w-full">
            <Textarea
                ref="textareaRef"
                v-model="draft"
                auto-resize
                class="pr-24 w-full"
                :placeholder="isEditing ? 'Edit the comment' : 'Write a comment'"
                rows="4"
                @keydown="handleKeydown"
            />

            <div class="right-3 bottom-3 gap-1 absolute flex items-center">
                <Button
                    v-tooltip.top="'Cancel'"
                    aria-label="Cancel"
                    icon="pi pi-times"
                    rounded
                    severity="secondary"
                    size="small"
                    text
                    @click="emit('cancel')"
                />
                <Button
                    v-tooltip.top="isEditing ? 'Save changes' : 'Save'"
                    :aria-label="isEditing ? 'Save changes' : 'Save'"
                    :icon="isEditing ? 'pi pi-check' : 'pi pi-send'"
                    rounded
                    size="small"
                    :disabled="!canSave"
                    :loading="isSaving"
                    @click="emit('save')"
                />
            </div>
        </div>
    </div>
</template>
