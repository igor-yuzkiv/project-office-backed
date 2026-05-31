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
            })
        })
    }

    return {
        require: baseConfirm.require,
        close: baseConfirm.close,
        requireAsync,
    }
}
