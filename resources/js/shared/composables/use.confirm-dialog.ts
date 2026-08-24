import { useConfirm as useBaseConfirm } from 'primevue/useconfirm'
import type { ConfirmationOptions } from 'primevue/confirmationoptions'

export function useConfirmDialog() {
    const baseConfirm = useBaseConfirm()

    async function requireAsync(options: Partial<ConfirmationOptions>): Promise<boolean> {
        return new Promise<boolean>((resolve) => {
            baseConfirm.require({
                header: 'Confirmation',
                ...options,
                accept: () => resolve(true),
                reject: () => resolve(false),
                // Dismissing with Esc or the close icon answers too: without this the
                // promise never settles, and a caller awaiting it waits forever.
                onHide: () => resolve(false),
            })
        })
    }

    return {
        require: baseConfirm.require,
        close: baseConfirm.close,
        requireAsync,
    }
}
