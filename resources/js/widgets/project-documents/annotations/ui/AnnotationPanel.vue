<script setup lang="ts">
import { computed, ref } from 'vue'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'
import type { IAnnotation } from '@/entities/annotation'
import { DataPanel, type DataPanelState } from '@/shared/components/data-panel'
import { formatDateTime } from '@/shared/utils/date.util'
import type { AnnotationAnchor } from '../composables/use.annotation-anchors'

const props = defineProps<{
    anchors: AnnotationAnchor[]
    isPending: boolean
    isError: boolean
    editingId: string | null
    reanchoringId: string | null
    currentUserId: string | null
}>()

const emit = defineEmits<{
    (e: 'select', annotation: IAnnotation): void
    (e: 'edit', annotation: IAnnotation): void
    (e: 'delete', annotation: IAnnotation): void
    (e: 'reanchor', annotation: IAnnotation): void
    (e: 'retry'): void
}>()

const SNIPPET_LENGTH = 180

const expanded = ref<string[]>([])

const state = computed<DataPanelState>(() => {
    if (props.isPending) return 'pending'
    if (props.isError) return 'error'
    if (props.anchors.length === 0) return 'empty'

    return 'ready'
})

function isExpanded(id: string): boolean {
    return expanded.value.includes(id)
}

function toggleExpanded(id: string) {
    expanded.value = isExpanded(id) ? expanded.value.filter((item) => item !== id) : [...expanded.value, id]
}

function isOwn(annotation: IAnnotation): boolean {
    return annotation.author.id === props.currentUserId
}
</script>

<template>
    <!-- No width, border or surface of its own, and nothing that hides it: the host decides where
         this list lives and how it goes away. -->
    <div class="flex h-full flex-col">
        <DataPanel
            title="Annotations"
            appearance="plain"
            :state="state"
            empty-message="No annotations yet. Click a block of the document, then write a comment below."
            error-message="Failed to load annotations."
            class="min-h-0 flex flex-1 flex-col"
            @retry="emit('retry')"
        >
            <div class="min-h-0 flex-1 overflow-y-auto">
                <!-- Rows rather than cards, like the activity stream and the document tree: the
                     sidebar is a list inside the workspace, not a stack of surfaces on top of it.
                     The amber left bar is the same hue the sheet uses while a block is being
                     picked, so the row and the document agree about what is happening. -->
                <article
                    v-for="anchor in anchors"
                    :key="anchor.annotation.id"
                    class="border-surface-100 dark:border-surface-800 hover:bg-surface-50 dark:hover:bg-surface-800/60 gap-2 px-4 py-3 flex cursor-pointer flex-col border-t border-l-2 border-l-transparent transition-colors first:border-t-0"
                    :class="{
                        'border-l-primary-500 bg-primary-50 dark:bg-primary-950/40': anchor.annotation.id === editingId,
                        'border-l-amber-500 bg-amber-50 dark:bg-amber-950/40': anchor.annotation.id === reanchoringId,
                        'opacity-60': anchor.block === null,
                    }"
                    @click="emit('select', anchor.annotation)"
                >
                    <div class="gap-2 flex items-center justify-between">
                        <div class="gap-2 min-w-0 flex items-center">
                            <!-- Avatar draws the label instead of the image when both are given. -->
                            <Avatar
                                :image="anchor.annotation.author.avatar_url ?? undefined"
                                :label="
                                    anchor.annotation.author.avatar_url ? undefined : anchor.annotation.author.initials
                                "
                                :pt="{ root: { class: '!bg-indigo-500 !text-white !text-xs !font-semibold' } }"
                                shape="circle"
                                size="normal"
                            />
                            <span class="text-sm text-surface-700 dark:text-surface-200 truncate">
                                {{ anchor.annotation.author.name }}
                            </span>
                        </div>
                        <span class="text-xs text-surface-400 shrink-0">
                            {{ formatDateTime(anchor.annotation.created_at) }}
                        </span>
                    </div>

                    <p v-if="anchor.annotation.text_snapshot" class="text-xs text-surface-500 line-clamp-2 italic">
                        {{ anchor.annotation.text_snapshot }}
                    </p>

                    <p class="text-sm text-surface-900 dark:text-surface-0 whitespace-pre-line">
                        {{
                            isExpanded(anchor.annotation.id) || anchor.annotation.content.length <= SNIPPET_LENGTH
                                ? anchor.annotation.content
                                : `${anchor.annotation.content.slice(0, SNIPPET_LENGTH)}…`
                        }}
                    </p>

                    <Button
                        v-if="anchor.annotation.content.length > SNIPPET_LENGTH"
                        class="p-0 w-fit"
                        :label="isExpanded(anchor.annotation.id) ? 'Show less' : 'Show more'"
                        severity="secondary"
                        size="small"
                        text
                        @click.stop="toggleExpanded(anchor.annotation.id)"
                    />

                    <p v-if="anchor.block === null" class="text-xs text-amber-600">Block not found</p>
                    <p v-else-if="anchor.kind === 'position'" class="text-xs text-surface-400">Block content changed</p>

                    <!-- Re-anchoring rewrites the annotation, so it follows the same rule as Edit and Delete. -->
                    <div v-if="isOwn(anchor.annotation)" class="gap-2 flex flex-wrap">
                        <Button
                            label="Re-anchor"
                            severity="secondary"
                            size="small"
                            text
                            @click.stop="emit('reanchor', anchor.annotation)"
                        />
                        <Button
                            label="Edit"
                            severity="secondary"
                            size="small"
                            text
                            @click.stop="emit('edit', anchor.annotation)"
                        />
                        <Button
                            label="Delete"
                            severity="danger"
                            size="small"
                            text
                            @click.stop="emit('delete', anchor.annotation)"
                        />
                    </div>
                </article>
            </div>
        </DataPanel>
    </div>
</template>
