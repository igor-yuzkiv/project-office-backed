import { definePreset } from '@primeuix/themes'
import Aura from '@primeuix/themes/aura'
import type { App } from 'vue'
import PrimeVue from 'primevue/config'
import ConfirmationService from 'primevue/confirmationservice'
import Ripple from 'primevue/ripple'
import ToastService from 'primevue/toastservice'
import Tooltip from 'primevue/tooltip'

import 'primeicons/primeicons.css'

const MyPreset = definePreset(Aura, {
    semantic: {
        colorScheme: {
            primary: {
                50: '{blue.50}',
                100: '{blue.100}',
                200: '{blue.200}',
                300: '{blue.300}',
                400: '{blue.400}',
                500: '{blue.500}',
                600: '{blue.600}',
                700: '{blue.700}',
                800: '{blue.800}',
                900: '{blue.900}',
                950: '{blue.950}',
            },
        },
    },
})

export default function primeVuePlugin(app: App) {
    app.use(PrimeVue, {
        theme: {
            preset: MyPreset,
            options: {
                darkModeSelector: '.dark',
                cssLayer: {
                    name: 'primevue',
                    order: 'theme, base, primevue',
                },
            },
        },
        ripple: true,
    })

    app.directive('ripple', Ripple)
    app.directive('tooltip', Tooltip)

    app.use(ToastService)
    app.use(ConfirmationService)
}
