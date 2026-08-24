<script setup lang="ts">
import { computed, ref, shallowRef, watch } from 'vue'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import ToggleSwitch from 'primevue/toggleswitch'
import type { IProjectDocument } from '@/entities/project-document/types'
import { DocumentSheet } from '@/widgets/project-documents/document-sheet'
import { AnnotationComposer, AnnotationSidebar, useAnnotationSession } from '@/widgets/project-documents/annotations'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { EMPTY_DOM_BLOCKS, type DomBlocks } from '@/shared/utils/markdown-anchor.dom.util'

const props = defineProps<{
    document: IProjectDocument
}>()

const authStore = useAuthStore()

// The sheet finds the blocks in its own DOM and hands them over; the session resolves
// annotations against them. shallowRef, because these hold live elements.
const blocks = shallowRef<DomBlocks>(EMPTY_DOM_BLOCKS)

// Annotating is what this screen is for, so it starts on. A reader who turns it off is
// turning it off for the document in front of them, not setting a preference.
const annotationsEnabled = ref(true)

watch(
    () => props.document.id,
    () => {
        annotationsEnabled.value = true
    }
)

const {
    selectedBlock,
    draft,
    editing,
    reanchoring,
    orderedAnchors,
    isPending,
    isError,
    isBusy,
    isReanchoring,
    isComposing,
    refetch,
    pickBlock,
    save,
    cancel,
    selectAnnotation,
    editAnnotation,
    startReanchoring,
    cancelReanchoring,
    removeAnnotation,
} = useAnnotationSession(
    () => props.document.id,
    () => blocks.value,
    // A document with no content renders no sheet, so there is nothing to annotate and
    // nothing to ask the server about.
    { enabled: () => annotationsEnabled.value && Boolean(props.document.content) }
)

const currentUserId = computed(() => authStore.user?.id ?? null)
</script>

<template>
    <div v-if="document.content" class="min-h-0 flex flex-1 overflow-hidden">
        <div class="min-h-0 flex flex-1 flex-col">
            <DocumentSheet
                :selected-block="selectedBlock"
                :content="document.content"
                :blocks-pickable="annotationsEnabled"
                @blocks-changed="blocks = $event"
                @pick-block="pickBlock"
            >
                <template #toolbar>
                    <label class="gap-2 text-surface-500 text-xs flex cursor-pointer items-center">
                        Annotations
                        <ToggleSwitch v-model="annotationsEnabled" />
                    </label>
                </template>

                <template #banner>
                    <div
                        v-if="annotationsEnabled && isReanchoring"
                        class="gap-3 rounded-lg p-3 bg-primary-50 dark:bg-primary-950 flex items-center justify-between"
                    >
                        <span class="text-sm text-surface-700 dark:text-surface-200">
                            Select the block this annotation belongs to.
                        </span>
                        <Button label="Cancel" severity="secondary" size="small" @click="cancelReanchoring" />
                    </div>

                    <p v-else-if="annotationsEnabled" class="text-xs text-surface-500">
                        Click a block of the document to comment on it.
                    </p>
                </template>
            </DocumentSheet>

            <!-- Docked like a chat composer: it appears once a block is picked, and never covers the text. -->
            <AnnotationComposer
                v-if="annotationsEnabled && isComposing"
                :key="selectedBlock?.descriptor.index"
                v-model:draft="draft"
                :is-editing="editing !== null"
                :is-saving="isBusy"
                @save="save"
                @cancel="cancel"
            />
        </div>

        <AnnotationSidebar
            v-if="annotationsEnabled"
            :anchors="orderedAnchors"
            :is-pending="isPending"
            :is-error="isError"
            :editing-id="editing?.id ?? null"
            :reanchoring-id="reanchoring?.id ?? null"
            :current-user-id="currentUserId"
            @select="selectAnnotation"
            @edit="editAnnotation"
            @delete="removeAnnotation($event.id)"
            @reanchor="startReanchoring"
            @retry="refetch()"
        />
    </div>

    <div v-else class="gap-3 p-10 flex flex-1 flex-col items-center justify-center text-center">
        <Icon icon="heroicons:document" class="text-surface-300 text-4xl" />
        <p class="text-surface-700 dark:text-surface-200 text-sm font-medium">This document has no content</p>
        <p class="text-surface-500 max-w-sm text-xs">
            It can stay a section that only holds nested documents, or you can write something in it.
        </p>
    </div>
</template>
