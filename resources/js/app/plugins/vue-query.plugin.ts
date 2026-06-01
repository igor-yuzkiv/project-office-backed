import { VueQueryPlugin } from '@tanstack/vue-query'
import type { App } from 'vue'

export default function (app: App) {
    app.use(VueQueryPlugin, {
        queryClientConfig: {
            defaultOptions: {
                queries: {
                    staleTime: 1000 * 60 * 5, // 5 minutes (data remains "fresh" for 5 mins)
                    gcTime: 1000 * 60 * 10, // 10 minutes (garbage collection/cache lifetime)
                    refetchOnWindowFocus: false, // Turn off auto-refetch when user switches tabs
                    refetchOnReconnect: true, // Re-fetch when network reconnects
                    retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 30000),
                },
            },
        },
    })
}
