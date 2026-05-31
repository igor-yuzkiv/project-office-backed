import { watch, type MaybeRef, type WatchSource } from 'vue'

export type ScrollOnSelectedOptions<TItem extends Record<string, unknown>> = {
    uniqField?: keyof TItem
    selectedField?: keyof TItem | Array<keyof TItem>
    isSelected?: (item: TItem) => boolean
    query?: string
    behavior?: ScrollBehavior
    block?: ScrollLogicalPosition
    inline?: ScrollLogicalPosition
    immediate?: boolean
    flush?: 'pre' | 'post' | 'sync'
}

const defaultOptions = {
    uniqField: 'id',
    selectedField: ['selected'],
    query: '[data-id="item-%%"]',
    behavior: 'smooth',
    block: 'center',
    inline: 'nearest',
    immediate: false,
    flush: 'post',
} as const

export function useScrollOnSelected<TItem extends Record<string, unknown>>(
    items: WatchSource<ReadonlyArray<TItem> | null | undefined>,
    options?: ScrollOnSelectedOptions<TItem>,
    isEnable: MaybeRef<boolean> = true
) {
    return watch(
        items,
        (newItems) => {
            const enabled = typeof isEnable === 'boolean' ? isEnable : isEnable.value
            if (!enabled) return

            const opts = { ...defaultOptions, ...(options || {}) }

            if (!Array.isArray(newItems) || newItems.length === 0) return

            let selectedItem: TItem | undefined

            if (opts.isSelected) {
                selectedItem = newItems.find(opts.isSelected)
            } else {
                const selectedFields = Array.isArray(opts.selectedField) ? opts.selectedField : [opts.selectedField]

                selectedItem = newItems.find((item) => selectedFields.some((field) => Boolean(item?.[field])))
            }

            if (!selectedItem) return

            const id = selectedItem[opts.uniqField as keyof TItem]
            if (id == null) return

            const selector = opts.query.replace('%%', String(id))
            const el = document.querySelector(selector)

            if (el instanceof HTMLElement) {
                el.scrollIntoView({
                    behavior: opts.behavior,
                    block: opts.block,
                    inline: opts.inline,
                })
            }
        },
        {
            immediate: options?.immediate ?? defaultOptions.immediate,
            flush: options?.flush ?? defaultOptions.flush,
        }
    )
}
