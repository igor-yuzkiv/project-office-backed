<script setup lang="ts">
import { computed, ref, useId, watch } from 'vue'
import { refDebounced } from '@vueuse/core'
import { Icon } from '@iconify/vue'
import InputText from 'primevue/inputtext'
import ProgressSpinner from 'primevue/progressspinner'
import {
    ICON_CATALOGUE_URL,
    ICON_SEARCH_ENDPOINT,
    ICON_SEARCH_LIMIT,
    ICON_SET_PREFIX,
    STARTER_ICONS,
} from '../icon-picker.config'

const emit = defineEmits<{
    (e: 'select', icon: string): void
}>()

/** The name currently written in the field, which may name an icon from any set. */
const typed = defineModel<string | null>({ required: true })

const fieldId = useId()

const query = ref('')
const debouncedQuery = refDebounced(query, 250)

const results = ref<string[]>([])
const isSearching = ref(false)
const hasFailed = ref(false)

// Answers can arrive out of order, and an older one must not overwrite what the reader is
// looking at now.
let latestRun = 0

const icons = computed(() => (debouncedQuery.value.trim() ? results.value : [...STARTER_ICONS]))

const isEmptyResult = computed(
    () => Boolean(debouncedQuery.value.trim()) && !isSearching.value && !hasFailed.value && results.value.length === 0
)

// Only the chosen set is searched, so a board of projects keeps one visual family. A name from
// any other set can still be typed into the field above.
watch(debouncedQuery, async (current) => {
    const term = current.trim()

    if (!term) {
        results.value = []
        hasFailed.value = false

        return
    }

    const run = ++latestRun

    isSearching.value = true
    hasFailed.value = false

    try {
        const params = new URLSearchParams({
            query: term,
            prefix: ICON_SET_PREFIX,
            limit: String(ICON_SEARCH_LIMIT),
        })
        const response = await fetch(`${ICON_SEARCH_ENDPOINT}?${params}`)

        if (!response.ok) throw new Error(String(response.status))

        const payload: { icons?: string[] } = await response.json()

        if (run !== latestRun) return

        results.value = payload.icons ?? []
    } catch {
        if (run !== latestRun) return

        // The catalogue is someone else's service: say so and keep the typed name usable.
        results.value = []
        hasFailed.value = true
    } finally {
        if (run === latestRun) isSearching.value = false
    }
})
</script>

<template>
    <div class="gap-3 p-1 w-80 flex flex-col">
        <div class="gap-2 flex flex-col">
            <label class="text-surface-600 dark:text-surface-300 text-xs font-medium" :for="fieldId"> Icon name </label>
            <div class="gap-2 flex items-center">
                <InputText
                    :id="fieldId"
                    v-model="typed"
                    placeholder="tabler:rocket"
                    maxlength="64"
                    size="small"
                    class="flex-1"
                />
                <span
                    class="border-surface-200 dark:border-surface-700 h-9 w-9 rounded-lg flex shrink-0 items-center justify-center border"
                >
                    <Icon v-if="typed" :icon="typed" class="text-surface-700 dark:text-surface-200 text-lg" />
                    <Icon v-else icon="tabler:square-rounded" class="text-surface-300 text-lg" />
                </span>
            </div>
        </div>

        <InputText v-model="query" placeholder="Search icons…" size="small" />

        <div v-if="isSearching" class="p-6 flex justify-center">
            <ProgressSpinner style="width: 1.75rem; height: 1.75rem" />
        </div>

        <p v-else-if="hasFailed" class="text-surface-500 px-1 text-xs">
            Could not reach the icon catalogue. You can still write a name above.
        </p>

        <p v-else-if="isEmptyResult" class="text-surface-500 px-1 text-xs">Nothing matched “{{ debouncedQuery }}”.</p>

        <div v-else class="gap-1 max-h-56 grid grid-cols-8 overflow-y-auto">
            <button
                v-for="icon in icons"
                :key="icon"
                type="button"
                class="hover:bg-surface-100 dark:hover:bg-surface-800 h-9 rounded-md flex items-center justify-center"
                :class="{ 'bg-surface-100 dark:bg-surface-800': icon === typed }"
                :title="icon"
                :aria-label="icon"
                @click="emit('select', icon)"
            >
                <Icon :icon="icon" class="text-surface-700 dark:text-surface-200 text-lg" />
            </button>
        </div>

        <a
            :href="ICON_CATALOGUE_URL"
            target="_blank"
            rel="noopener"
            class="text-primary gap-1 px-1 text-xs inline-flex items-center"
        >
            Browse the full catalogue
            <Icon icon="heroicons:arrow-up-right" class="size-3" />
        </a>
    </div>
</template>
