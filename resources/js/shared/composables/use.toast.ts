import { useToast as usePrimeToast } from 'primevue/usetoast'
import type { ToastMessageOptions } from 'primevue/toast'
import { type MaybeRefOrGetter, toValue } from 'vue'

type ToastInput = ToastMessageOptions | string

export function useToast(defaultLife: MaybeRefOrGetter<number> = 5000) {
    const toast = usePrimeToast()

    function normalizeOptions(input: ToastInput): ToastMessageOptions {
        if (typeof input === 'string') {
            return { detail: input }
        }

        return input
    }

    function add(options: ToastMessageOptions): void
    function add(detail: string): void
    function add(input: ToastInput) {
        toast.add({
            life: toValue(defaultLife),
            ...normalizeOptions(input),
        })
    }

    function success(options: ToastMessageOptions): void
    function success(detail: string): void
    function success(input: ToastInput) {
        add({
            severity: 'success',
            summary: 'Success',
            ...normalizeOptions(input),
        })
    }

    function error(options: ToastMessageOptions): void
    function error(detail: string): void
    function error(input: ToastInput) {
        add({
            severity: 'error',
            summary: 'Error',
            ...normalizeOptions(input),
        })
    }

    function warn(options: ToastMessageOptions): void
    function warn(detail: string): void
    function warn(input: ToastInput) {
        add({
            severity: 'warn',
            summary: 'Warning',
            ...normalizeOptions(input),
        })
    }

    function info(options: ToastMessageOptions): void
    function info(detail: string): void
    function info(input: ToastInput) {
        add({
            severity: 'info',
            summary: 'Info',
            ...normalizeOptions(input),
        })
    }

    function removeGroup(group: string) {
        toast.removeGroup(group)
    }

    function removeAll() {
        toast.removeAllGroups()
    }

    return {
        add,
        success,
        error,
        warn,
        info,
        removeGroup,
        removeAll,
    }
}
