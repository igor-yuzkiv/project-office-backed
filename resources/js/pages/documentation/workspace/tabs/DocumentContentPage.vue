<script setup lang="ts">
import { computed, ref, shallowRef, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import ToggleSwitch from 'primevue/toggleswitch'
import type { IAnnotation } from '@/entities/annotation'
import type { IProjectDocument } from '@/entities/project-document/types'
import { DocumentCanvas } from '@/shared/components/document-canvas'
import { DocumentSheet } from '@/shared/components/document-sheet'
import { AnnotationComposer, AnnotationPanel, useAnnotationSession } from '@/widgets/project-documents/annotations'
import {
    DocumentVersionIndicator,
    DocumentVersionSwitcher,
    useOpenDocumentVersion,
} from '@/widgets/project-documents/versions'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { SideTab, SideTabs } from '@/shared/components/side-tabs'
import { useTabbedSidePanel } from '@/shared/composables'
import { EMPTY_DOM_BLOCKS, type DomBlocks } from '@/shared/utils/markdown-anchor.dom.util'

const props = defineProps<{
    document: IProjectDocument
}>()

const router = useRouter()
const authStore = useAuthStore()

const { versions, openVersionId, openVersion, selectVersion } = useOpenDocumentVersion(() => props.document.id)

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
    () => openVersion.value?.id ?? '',
    () => blocks.value,
    // A document with no versions renders no sheet, so there is nothing to annotate and
    // nothing to ask the server about.
    { enabled: () => annotationsEnabled.value && openVersion.value !== null }
)

const currentUserId = computed(() => authStore.user?.id ?? null)

const sidebar = useTabbedSidePanel('docs:sidebar')

const annotationPanelProps = computed(() => ({
    anchors: orderedAnchors.value,
    isPending: isPending.value,
    isError: isError.value,
    editingId: editing.value?.id ?? null,
    reanchoringId: reanchoring.value?.id ?? null,
    currentUserId: currentUserId.value,
}))

function openEditor() {
    router.push({
        name: 'project-documentation.document.edit',
        params: { projectId: props.document.project_id, documentId: props.document.id },
    })
}

const annotationPanelHandlers = {
    select: selectAnnotation,
    edit: editAnnotation,
    delete: (annotation: IAnnotation) => removeAnnotation(annotation.id),
    reanchor: startReanchoring,
    retry: () => refetch(),
}
</script>

<template>
    <div v-if="openVersion" class="min-h-0 flex flex-1 overflow-hidden">
        <!-- min-w-0: without it this column is as wide as its widest child, and one unbreakable
             code block in the document would push the sidebar off the screen. -->
        <div class="min-h-0 min-w-0 flex flex-1 flex-col">
            <DocumentCanvas>
                <template #start>
                    <div class="gap-2 flex flex-wrap items-center">
                        <DocumentVersionSwitcher
                            :versions="versions"
                            :open-version-id="openVersionId"
                            @open="selectVersion"
                        />

                        <!-- Wrapping rather than clipping: the column is narrow whenever the tree and a
                             side panel are both open, and the toolbar has to survive that. -->
                        <label class="gap-2 text-surface-500 text-xs ml-auto flex cursor-pointer items-center">
                            Annotations
                            <ToggleSwitch v-model="annotationsEnabled" />
                        </label>
                    </div>

                    <div
                        v-if="annotationsEnabled && isReanchoring"
                        class="gap-3 rounded-lg p-3 bg-primary-50 dark:bg-primary-950 flex items-center justify-between"
                    >
                        <span class="text-sm text-surface-700 dark:text-surface-200">
                            Select the block this annotation belongs to.
                        </span>
                        <Button label="Cancel" severity="secondary" size="small" @click="cancelReanchoring" />
                    </div>

                    <DocumentVersionIndicator v-else :version="openVersion" />
                </template>

                <DocumentSheet
                    :selected-block="selectedBlock"
                    :content="openVersion.content ?? ''"
                    :blocks-pickable="annotationsEnabled"
                    show-catalog
                    @blocks-changed="blocks = $event"
                    @pick-block="pickBlock"
                />
            </DocumentCanvas>

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

        <SideTabs v-if="annotationsEnabled" :panel="sidebar" side="right" width="24rem">
            <SideTab value="annotations" icon="heroicons:chat-bubble-left" label="Annotations">
                <AnnotationPanel v-bind="annotationPanelProps" v-on="annotationPanelHandlers" />
            </SideTab>
        </SideTabs>
    </div>

    <div v-else class="gap-3 p-10 flex flex-1 flex-col items-center justify-center text-center">
        <Icon icon="heroicons:document" class="text-surface-300 text-4xl" />
        <p class="text-surface-700 dark:text-surface-200 text-sm font-medium">This document has no versions yet</p>
        <p class="text-surface-500 max-w-sm text-xs">
            It can stay a section that only holds nested documents, or you can write a first version of it.
        </p>
        <Button label="Create a version" size="small" outlined @click="openEditor()" />
    </div>
</template>
