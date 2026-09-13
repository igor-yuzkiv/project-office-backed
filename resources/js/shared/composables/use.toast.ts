import { useToast as usePrimeToast } from 'primevue/usetoast'
import type { ToastMessageOptions } from 'primevue/toast'
import { type MaybeRefOrGetter, toValue } from 'vue'
import type { RouteLocationRaw } from 'vue-router'

export interface ToastLink {
    label: string
    to: RouteLocationRaw
}

/** A toast is one line of `detail` and, optionally, a link after it — rendered by the Toast in App.vue. */
export type ToastMessage = ToastMessageOptions & { link?: ToastLink }

type ToastInput = ToastMessage | string

export function useToast(defaultLife: MaybeRefOrGetter<number> = 5000) {
    const toast = usePrimeToast()

    function normalizeOptions(input: ToastInput): ToastMessage {
        if (typeof input === 'string') {
            return { detail: input }
        }

        return input
    }

    function add(options: ToastMessage): void
    function add(detail: string): void
    function add(input: ToastInput) {
        toast.add({
            life: toValue(defaultLife),
            ...normalizeOptions(input),
        })
    }

    function success(options: ToastMessage): void
    function success(detail: string): void
    function success(input: ToastInput) {
        add({
            severity: 'success',
            summary: 'Success',
            ...normalizeOptions(input),
        })
    }

    function error(options: ToastMessage): void
    function error(detail: string): void
    function error(input: ToastInput) {
        add({
            severity: 'error',
            summary: 'Error',
            ...normalizeOptions(input),
        })
    }

    function warn(options: ToastMessage): void
    function warn(detail: string): void
    function warn(input: ToastInput) {
        add({
            severity: 'warn',
            summary: 'Warning',
            ...normalizeOptions(input),
        })
    }

    function info(options: ToastMessage): void
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
