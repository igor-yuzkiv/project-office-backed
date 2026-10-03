<script setup lang="ts">
import { ref } from 'vue'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'
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
    /** A list to read, not to act on: no selecting, no editing, and nothing said about anchors. */
    readonly?: boolean
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
    <div class="bg-page flex h-full flex-col">
        <header class="gap-2 h-11 px-4 hairline flex shrink-0 items-center">
            <h2 class="type-meta">Annotations</h2>
            <span v-if="!isPending && !isError" class="type-meta-3 ml-auto tabular-nums">{{ anchors.length }}</span>
        </header>

        <p v-if="isPending" class="type-meta-3 px-4 py-3">Loading annotations…</p>

        <div v-else-if="isError" class="gap-2 px-4 py-3 flex flex-col items-start">
            <p class="type-meta-3">Failed to load annotations.</p>
            <Button label="Try again" size="small" severity="secondary" @click="emit('retry')" />
        </div>

        <p v-else-if="anchors.length === 0" class="type-meta-3 px-4 py-3">
            {{ readonly ? 'No annotations yet.' : 'No annotations yet. Click a block of the document to write one.' }}
        </p>

        <div v-else class="min-h-0 flex-1 overflow-y-auto text-[13px]">
            <!-- Rows rather than cards, like the document tree: the sidebar is a list inside the
                 workspace, not a stack of surfaces on top of it. The amber left bar is the same hue
                 the sheet uses while a block is being picked, so the row and the document agree
                 about what is happening. -->
            <article
                v-for="anchor in anchors"
                :key="anchor.annotation.id"
                class="gap-1.5 px-4 py-3 hairline flex flex-col border-l-2 border-l-transparent transition-colors"
                :class="{
                    'hover:bg-hover cursor-pointer': !readonly,
                    'border-l-accent bg-hover': anchor.annotation.id === editingId,
                    'border-l-amber-500 bg-amber-50 dark:bg-amber-950/40': anchor.annotation.id === reanchoringId,
                    'opacity-60': !readonly && anchor.block === null,
                }"
                @click="readonly || emit('select', anchor.annotation)"
            >
                <div class="gap-2 flex items-center justify-between">
                    <div class="gap-2 min-w-0 flex items-center">
                        <!-- Avatar draws the label instead of the image when both are given. -->
                        <Avatar
                            :image="anchor.annotation.author.avatar_url ?? undefined"
                            :label="anchor.annotation.author.avatar_url ? undefined : anchor.annotation.author.initials"
                            :pt="{ root: { class: '!bg-accent !text-accent-ink !text-[11px] !font-semibold !size-6' } }"
                            shape="circle"
                            size="normal"
                        />
                        <span class="text-ink font-medium truncate">{{ anchor.annotation.author.name }}</span>
                    </div>
                    <span class="type-meta-3 shrink-0">{{ formatDateTime(anchor.annotation.created_at) }}</span>
                </div>

                <p v-if="anchor.annotation.text_snapshot" class="type-meta-3 line-clamp-2 italic">
                    {{ anchor.annotation.text_snapshot }}
                </p>

                <p class="text-ink whitespace-pre-line">
                    {{
                        isExpanded(anchor.annotation.id) || anchor.annotation.content.length <= SNIPPET_LENGTH
                            ? anchor.annotation.content
                            : `${anchor.annotation.content.slice(0, SNIPPET_LENGTH)}…`
                    }}
                </p>

                <button
                    v-if="anchor.annotation.content.length > SNIPPET_LENGTH"
                    type="button"
                    class="type-meta hover:text-ink w-fit cursor-pointer"
                    @click.stop="toggleExpanded(anchor.annotation.id)"
                >
                    {{ isExpanded(anchor.annotation.id) ? 'Show less' : 'Show more' }}
                </button>

                <template v-if="!readonly">
                    <p v-if="anchor.block === null" class="type-meta-3 text-amber-700 dark:text-amber-400">
                        Block not found
                    </p>
                    <p v-else-if="anchor.kind === 'position'" class="type-meta-3">Block content changed</p>
                </template>

                <!-- Re-anchoring rewrites the annotation, so it follows the same rule as Edit and Delete. -->
                <div v-if="!readonly && isOwn(anchor.annotation)" class="gap-3 flex flex-wrap">
                    <button
                        type="button"
                        class="type-meta hover:text-ink cursor-pointer"
                        @click.stop="emit('reanchor', anchor.annotation)"
                    >
                        Re-anchor
                    </button>
                    <button
                        type="button"
                        class="type-meta hover:text-ink cursor-pointer"
                        @click.stop="emit('edit', anchor.annotation)"
                    >
                        Edit
                    </button>
                    <button
                        type="button"
                        class="type-meta hover:text-red-500 cursor-pointer"
                        @click.stop="emit('delete', anchor.annotation)"
                    >
                        Delete
                    </button>
                </div>
            </article>
        </div>
    </div>
</template>
