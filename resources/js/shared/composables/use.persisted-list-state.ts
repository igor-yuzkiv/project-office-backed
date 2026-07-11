import { watch } from 'vue'
import { useStorage, type Serializer } from '@vueuse/core'
import { useRoute } from 'vue-router'

interface UsePersistedListStateOptions {
    key?: string
    ttlMs?: number
    storage?: 'session' | 'local'
    /** Rejects a restored snapshot (e.g. stale schema, tampered storage) before it is applied. */
    validate?: (data: Record<string, unknown>) => boolean
}

interface StorageEnvelope<T> {
    data: T
    expiresAt: number
}

type PersistableState = Record<string, { value: unknown }>

function ttlSerializer<T>(): Serializer<StorageEnvelope<T> | null> {
    return {
        read: (raw) => {
            const parsed = JSON.parse(raw) as StorageEnvelope<T>
            return parsed.expiresAt > Date.now() ? parsed : null
        },
        write: (value) => JSON.stringify(value),
    }
}

const DEFAULT_TTL_MS = 24 * 60 * 60 * 1000

/**
 * Persists a set of reactive values (e.g. filters, sort) to browser storage keyed by page,
 * and restores them on load. Values must be JSON-serializable.
 */
export function usePersistedListState(state: PersistableState, options: UsePersistedListStateOptions = {}): void {
    const route = useRoute()

    const storageKey = `list-state:${options.key ?? route.path}`
    const ttlMs = options.ttlMs ?? DEFAULT_TTL_MS
    const storageArea = options.storage === 'local' ? localStorage : sessionStorage

    const stored = useStorage<StorageEnvelope<Record<string, unknown>> | null>(storageKey, null, storageArea, {
        serializer: ttlSerializer(),
    })

    if (stored.value && (!options.validate || options.validate(stored.value.data))) {
        for (const [key, ref] of Object.entries(state)) {
            if (key in stored.value.data) ref.value = stored.value.data[key]
        }
    } else {
        storageArea.removeItem(storageKey)
    }

    watch(
        () => Object.fromEntries(Object.entries(state).map(([key, ref]) => [key, ref.value])),
        (snapshot) => {
            stored.value = { data: snapshot, expiresAt: Date.now() + ttlMs }
        }
    )
}
