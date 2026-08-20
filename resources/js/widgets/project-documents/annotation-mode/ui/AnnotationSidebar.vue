<script setup lang="ts">
import { computed, ref } from 'vue'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import type { IAnnotation } from '@/entities/annotation'
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

const isEmpty = computed(() => !props.isPending && !props.isError && props.anchors.length === 0)
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
    <aside
        class="border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-900 w-96 flex h-full shrink-0 flex-col border-l"
    >
        <h2 class="p-4 font-semibold text-surface-900 dark:text-surface-0 shrink-0">Annotations</h2>

        <div class="gap-3 px-4 pb-4 min-h-0 flex flex-1 flex-col overflow-y-auto">
            <template v-if="isPending">
                <Skeleton v-for="n in 3" :key="n" height="6rem" />
            </template>

            <div v-else-if="isError" class="gap-2 flex flex-col items-start">
                <p class="text-sm text-red-500">Failed to load annotations.</p>
                <Button label="Try again" severity="secondary" size="small" @click="emit('retry')" />
            </div>

            <p v-else-if="isEmpty" class="text-sm text-surface-400">
                No annotations yet. Click a block of the document, then write a comment below.
            </p>

            <article
                v-for="anchor in anchors"
                :key="anchor.annotation.id"
                class="gap-2 rounded-lg p-3 bg-white dark:bg-surface-950 border-surface-200 dark:border-surface-700 flex cursor-pointer flex-col border transition-colors"
                :class="{
                    'border-primary-500': anchor.annotation.id === editingId,
                    'opacity-60': anchor.element === null,
                    'ring-primary-500 ring-2': anchor.annotation.id === reanchoringId,
                }"
                @click="emit('select', anchor.annotation)"
            >
                <div class="gap-2 flex items-center justify-between">
                    <div class="gap-2 min-w-0 flex items-center">
                        <!-- Avatar draws the label instead of the image when both are given. -->
                        <Avatar
                            :image="anchor.annotation.author.avatar_url ?? undefined"
                            :label="anchor.annotation.author.avatar_url ? undefined : anchor.annotation.author.initials"
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

                <p v-if="anchor.element === null" class="text-xs text-amber-600">Block not found</p>
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
    </aside>
</template>
