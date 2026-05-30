import { VueQueryPlugin } from '@tanstack/vue-query'
import type { App } from 'vue'

export default function (app: App) {
    app.use(VueQueryPlugin, {
        queryClientConfig: {
            defaultOptions: {
                queries: {
                    refetchOnWindowFocus: false,
                    retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 30000),
                },
            },
        },
    })
}
