<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { refDebounced } from '@vueuse/core'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import { ColorPicker } from 'vue3-colorpicker'
import 'vue3-colorpicker/style.css'

import type { ITag } from '@/entities/tag/types'
import { useTagsSearchQuery } from '@/entities/tag/queries'
import { useCreateTagMutation } from '@/entities/tag/mutations'
import { randomHex } from '@/shared/utils/color.util'
import type { HexColor } from '@/shared/types'

import TagBadge from '../../metadata/ui/TagBadge.vue'

const visible = defineModel<boolean>('visible', { required: true })
const selectedTags = defineModel<ITag[]>({ required: true })

const searchQuery = ref('')
const trimmedSearchQuery = computed(() => searchQuery.value.trim())
const debouncedSearchQuery = refDebounced(trimmedSearchQuery, 300)

const pendingColor = ref<HexColor>(randomHex())

const { tags: searchResults } = useTagsSearchQuery(debouncedSearchQuery)
const { mutate: createTag, isPending: isCreatingTag } = useCreateTagMutation()

const selectedTagIds = computed(() => {
    return new Set(selectedTags.value.map((tag) => tag.id))
})

const exactTagExists = computed(() => {
    const search = trimmedSearchQuery.value.toLowerCase()

    if (!search) return false

    return searchResults.value.some((tag) => tag.name.toLowerCase() === search)
})

const isCreateMode = computed(() => {
    return trimmedSearchQuery.value !== '' && !exactTagExists.value
})

const availableTags = computed<ITag[]>(() => {
    return searchResults.value.filter((tag) => !selectedTagIds.value.has(tag.id))
})

function selectTag(tag: ITag) {
    if (selectedTagIds.value.has(tag.id)) return

    selectedTags.value = [...selectedTags.value, tag]
}

function removeTag(tagId: string) {
    selectedTags.value = selectedTags.value.filter((tag) => tag.id !== tagId)
}

function resetCreateState() {
    searchQuery.value = ''
    pendingColor.value = randomHex()
}

function onCreateTag() {
    const name = trimmedSearchQuery.value

    if (!name || isCreatingTag.value) return

    createTag(
        {
            name,
            color: pendingColor.value,
        },
        {
            onSuccess(response) {
                selectTag(response.data)
                resetCreateState()
            },
        }
    )
}

function onSearchKeydown(event: KeyboardEvent) {
    if (event.key === 'Enter' && isCreateMode.value) {
        onCreateTag()
    }
}

watch(visible, (isVisible) => {
    if (!isVisible) return

    resetCreateState()
})
</script>

<template>
    <Dialog v-model:visible="visible" modal :closable="true" :style="{ width: '34rem' }">
        <template #header>
            <div class="gap-2 flex items-center">
                <i class="pi pi-tag text-primary" />
                <span class="text-base font-semibold">Manage Record Tags</span>
            </div>
        </template>

        <div class="gap-4 py-1 flex flex-col">
            <div class="gap-3 flex flex-col">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold tracking-wide text-surface-500 uppercase"> Selected Tags </span>
                    <span class="text-xs text-surface-500"> {{ selectedTags.length }} selected </span>
                </div>

                <div v-if="selectedTags.length > 0" class="gap-2 flex flex-wrap max-h-40 overflow-y-auto">
                    <TagBadge
                        v-for="tag in selectedTags"
                        :key="tag.id"
                        :tag="tag"
                        class="cursor-pointer"
                        closable
                        @click="removeTag(tag.id)"
                    />
                </div>

                <p v-else class="text-sm text-surface-400">No tags selected</p>
            </div>

            <div class="gap-2 flex items-center">
                <InputText
                    v-model="searchQuery"
                    placeholder="Search existing tags or create a new one..."
                    class="w-full"
                    @keydown="onSearchKeydown"
                />

                <div v-if="isCreateMode" class="gap-2 flex items-center">
                    <ColorPicker v-model:pureColor="pendingColor" format="hex" shape="circle" />

                    <Button
                        label="Create"
                        size="small"
                        :loading="isCreatingTag"
                        :disabled="!trimmedSearchQuery"
                        @click="onCreateTag"
                    />
                </div>
            </div>

            <div class="gap-2 flex flex-col">
                <span class="text-xs font-semibold tracking-wide text-surface-500 uppercase"> Available Tags </span>

                <div class="max-h-60 divide-surface-200 flex flex-col divide-y overflow-y-auto">
                    <div
                        v-for="tag in availableTags"
                        :key="tag.id"
                        class="gap-3 hover:bg-surface-50 px-1 py-2 flex cursor-pointer items-center"
                        @click="selectTag(tag)"
                    >
                        <span class="h-5 w-5 flex shrink-0 rounded-full" :style="{ backgroundColor: tag.color }" />

                        <span class="text-sm flex-1">
                            {{ tag.name }}
                        </span>
                    </div>

                    <div v-if="availableTags.length === 0" class="py-3 text-sm text-surface-400 text-center">
                        <template v-if="isCreateMode">
                            <i class="pi pi-plus mr-1" />
                            Type a tag name above to create a new tag
                        </template>

                        <template v-else-if="trimmedSearchQuery"> No matching tags </template>

                        <template v-else> Start typing to search tags </template>
                    </div>
                </div>
            </div>
        </div>
    </Dialog>
</template>
