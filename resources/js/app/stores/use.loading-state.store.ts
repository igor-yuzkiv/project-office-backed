import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

export interface LoadingEntry {
    title?: string
    subtitle?: string
    count: number
}

export const useLoadingStateStore = defineStore('loading-state', () => {
    const loaders = ref(new Map<string, LoadingEntry>())

    const isLoading = computed(() => loaders.value.size > 0)

    const currentLoader = computed(() => {
        const entries = Array.from(loaders.value.values())

        if (!entries.length) {
            return null
        }

        const first = entries[0]
        const moreCount = entries.length - 1

        return {
            ...first,
            title: moreCount > 0 ? `${first.title ?? 'Loading'} and ${moreCount} more` : (first.title ?? 'Loading'),
        }
    })

    function start(key: string, payload: Omit<LoadingEntry, 'count'> = {}) {
        const current = loaders.value.get(key)

        loaders.value.set(key, {
            title: payload.title ?? current?.title,
            subtitle: payload.subtitle ?? current?.subtitle,
            count: (current?.count ?? 0) + 1,
        })
    }

    function stop(key: string) {
        const current = loaders.value.get(key)

        if (!current) {
            return
        }

        if (current.count <= 1) {
            loaders.value.delete(key)
            return
        }

        loaders.value.set(key, {
            ...current,
            count: current.count - 1,
        })
    }

    function toggle(key: string, payload: Omit<LoadingEntry, 'count'> = {}) {
        if (loaders.value.has(key)) {
            stop(key)
        } else {
            start(key, payload)
        }
    }

    function has(key: string) {
        return loaders.value.has(key)
    }

    function get(key: string) {
        return loaders.value.get(key)
    }

    async function run<T>(key: string, payload: Omit<LoadingEntry, 'count'>, callback: () => Promise<T>): Promise<T> {
        start(key, payload)

        try {
            return await callback()
        } finally {
            stop(key)
        }
    }

    function clear() {
        loaders.value.clear()
    }

    return {
        loaders,
        currentLoader,
        isLoading,
        start,
        stop,
        toggle,
        has,
        get,
        run,
        clear,
    }
})
