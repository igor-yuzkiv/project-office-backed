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
    <div
        class="p-4 border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-900 shadow-lg shrink-0 border-t"
    >
        <div class="gap-2 max-w-5xl mx-auto flex w-full flex-col">
            <div class="gap-2 text-xs text-surface-500 flex items-center justify-between">
                <span>{{ isEditing ? 'Editing an annotation' : `Commenting on: ${blockLabel}` }}</span>
                <span class="text-surface-400">Enter to save · Shift+Enter for a new line · Esc to close</span>
            </div>

            <div class="gap-2 flex items-end">
                <Textarea
                    ref="textareaRef"
                    v-model="draft"
                    auto-resize
                    class="flex-1"
                    placeholder="Write a comment"
                    rows="2"
                    @keydown="handleKeydown"
                />

                <div class="gap-2 flex flex-col">
                    <Button
                        :label="isEditing ? 'Save changes' : 'Save'"
                        size="small"
                        :disabled="!canSave"
                        :loading="isSaving"
                        @click="emit('save')"
                    />
                    <Button label="Cancel" severity="secondary" size="small" text @click="emit('cancel')" />
                </div>
            </div>
        </div>
    </div>
</template>
