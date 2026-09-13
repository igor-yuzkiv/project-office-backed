import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import { useCopyableKey } from './use.copyable-key'

const copy = vi.fn(() => Promise.resolve())
const copied = ref(false)
const toastAdd = vi.fn()

vi.mock('@vueuse/core', () => ({
    useClipboard: () => ({ copy, copied }),
}))

vi.mock('primevue/usetoast', () => ({
    useToast: () => ({ add: toastAdd }),
}))

describe('useCopyableKey', () => {
    beforeEach(() => {
        copy.mockClear()
        toastAdd.mockClear()
    })

    it('copies the key and reports it in a toast', async () => {
        const { copyKey } = useCopyableKey('MTM-1')

        await copyKey()

        expect(copy).toHaveBeenCalledWith('MTM-1')
        expect(toastAdd).toHaveBeenCalledWith(expect.objectContaining({ severity: 'success', detail: 'Copied MTM-1' }))
    })

    it('reads the current key when it is a getter', async () => {
        const key = ref('MTM-1')
        const { copyKey } = useCopyableKey(() => key.value)
        key.value = 'MTM-2'

        await copyKey()

        expect(copy).toHaveBeenCalledWith('MTM-2')
        expect(toastAdd).toHaveBeenCalledWith(expect.objectContaining({ detail: 'Copied MTM-2' }))
    })

    it('exposes the clipboard copied state', () => {
        const { copied: state } = useCopyableKey('MTM-1')

        expect(state).toBe(copied)
    })
})
