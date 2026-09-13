import { useClipboard } from '@vueuse/core'
import { type MaybeRefOrGetter, toValue } from 'vue'
import { useToast } from '@/shared/composables/use.toast'

const TOAST_LIFE = 2000

export function useCopyableKey(value: MaybeRefOrGetter<string>) {
    const { copy, copied } = useClipboard()
    const toast = useToast()

    async function copyKey() {
        const key = toValue(value)
        await copy(key)
        toast.add({ severity: 'success', detail: `Copied ${key}`, life: TOAST_LIFE })
    }

    return { copyKey, copied }
}
