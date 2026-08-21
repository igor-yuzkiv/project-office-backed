<script setup lang="ts">
import { computed, nextTick, onMounted, useTemplateRef, type ComponentPublicInstance } from 'vue'
import { useFocus } from '@vueuse/core'
import Button from 'primevue/button'
import Textarea from 'primevue/textarea'

const props = defineProps<{
    isEditing: boolean
    isSaving: boolean
}>()

const emit = defineEmits<{
    (e: 'save'): void
    (e: 'cancel'): void
}>()

const draft = defineModel<string>('draft', { required: true })

// useFocus resolves a component instance to its own element, so PrimeVue's Textarea
// can be handed over as it is.
const input = useTemplateRef<ComponentPublicInstance>('input')
const { focused } = useFocus(input)

const canSave = computed(() => draft.value.trim().length > 0 && !props.isSaving)

function focus() {
    nextTick(() => (focused.value = true))
}

/** Enter sends, Shift+Enter breaks the line — the shape everyone already knows from chat. */
function handleKeydown(event: KeyboardEvent) {
    if (event.key !== 'Enter' || event.shiftKey) return

    event.preventDefault()

    if (canSave.value) emit('save')
}

// The parent remounts this bar per selected block, so mounting is when focus belongs.
onMounted(focus)
</script>

<template>
    <div class="p-4 border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-900 shrink-0 border-t">
        <!-- The actions live inside the field, so the bar stays one object rather than a form. -->
        <div class="max-w-5xl relative mx-auto w-full">
            <Textarea
                ref="input"
                v-model="draft"
                auto-resize
                class="pr-24 max-h-48 w-full overflow-y-auto"
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
                    text
                    :disabled="!canSave"
                    :loading="isSaving"
                    @click="emit('save')"
                />
            </div>
        </div>
    </div>
</template>
