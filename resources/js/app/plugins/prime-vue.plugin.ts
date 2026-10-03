import { definePreset } from '@primeuix/themes'
import Aura from '@primeuix/themes/aura'
import type { App } from 'vue'
import PrimeVue from 'primevue/config'
import ConfirmationService from 'primevue/confirmationservice'
import Ripple from 'primevue/ripple'
import ToastService from 'primevue/toastservice'
import Tooltip from 'primevue/tooltip'

import 'primeicons/primeicons.css'

/**
 * The JetBrains Islands Light / Islands Darcula palette expressed as the PrimeVue semantic scheme.
 * The surface scale carries the page/canvas/hover/line/ink steps so the `surface-*` Tailwind
 * utilities from tailwindcss-primeui pick them up; the semantic aliases point at the same steps.
 *
 * Light: 0 page · 50 canvas · 100 hover · 300 line · 400 line-2 · 500 ink-3 · 600 ink-2 · 900 ink.
 * Dark:  950 page · 900 canvas · 800 hover · 700 line · 600 line-2 · 400 ink-3 · 300 ink-2 · 50 ink.
 */
const MyPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: '#EDF3FF',
            100: '#D4E2FF',
            200: '#ADC8FF',
            300: '#89AFFF',
            400: '#548AF7',
            500: '#3574F0',
            600: '#2462D9',
            700: '#1E50B8',
            800: '#1A4192',
            900: '#173670',
            950: '#0F2247',
        },
        focusRing: {
            width: '2px',
            style: 'solid',
            color: '{primary.color}',
            offset: '2px',
        },
        colorScheme: {
            light: {
                surface: {
                    0: '#FFFFFF',
                    50: '#F7F8FA',
                    100: '#EBECF0',
                    200: '#DFE1E5',
                    300: '#C9CCD6',
                    400: '#A8ADBD',
                    500: '#6C707E',
                    600: '#4E5157',
                    700: '#3B3E44',
                    800: '#2B2D30',
                    900: '#1E1F22',
                    950: '#000000',
                },
                primary: {
                    color: '{primary.500}',
                    contrastColor: '#ffffff',
                    hoverColor: '{primary.600}',
                    activeColor: '{primary.700}',
                },
                highlight: {
                    background: '{primary.100}',
                    focusBackground: '{primary.200}',
                    color: '{primary.700}',
                    focusColor: '{primary.800}',
                },
                text: {
                    color: '{surface.900}',
                    hoverColor: '{surface.950}',
                    mutedColor: '{surface.600}',
                    hoverMutedColor: '{surface.700}',
                },
                content: {
                    background: '{surface.0}',
                    hoverBackground: '{surface.100}',
                    borderColor: '{surface.300}',
                    color: '{text.color}',
                    hoverColor: '{text.hover.color}',
                },
                formField: {
                    background: '{surface.0}',
                    borderColor: '{surface.400}',
                    hoverBorderColor: '{surface.500}',
                    focusBorderColor: '{primary.color}',
                    color: '{surface.900}',
                    placeholderColor: '{surface.500}',
                    iconColor: '{surface.500}',
                    shadow: 'none',
                },
                overlay: {
                    select: { background: '{surface.0}', borderColor: '{surface.400}', color: '{text.color}' },
                    popover: { background: '{surface.0}', borderColor: '{surface.400}', color: '{text.color}' },
                    modal: { background: '{surface.0}', borderColor: '{surface.400}', color: '{text.color}' },
                },
            },
            dark: {
                surface: {
                    0: '#FFFFFF',
                    50: '#DFE1E5',
                    100: '#CED0D6',
                    200: '#B4B8BF',
                    300: '#A8ADB5',
                    400: '#9599A0',
                    500: '#6F737A',
                    600: '#5A5D63',
                    700: '#43454A',
                    800: '#393B40',
                    900: '#2B2D30',
                    950: '#1E1F22',
                },
                primary: {
                    color: '{primary.500}',
                    contrastColor: '#ffffff',
                    hoverColor: '{primary.400}',
                    activeColor: '{primary.300}',
                },
                highlight: {
                    background: '#2E436E',
                    focusBackground: '#35538A',
                    color: '#A9C5FF',
                    focusColor: '#C8DAFF',
                },
                text: {
                    color: '{surface.50}',
                    hoverColor: '{surface.0}',
                    mutedColor: '{surface.300}',
                    hoverMutedColor: '{surface.200}',
                },
                content: {
                    background: '{surface.950}',
                    hoverBackground: '{surface.800}',
                    borderColor: '{surface.700}',
                    color: '{text.color}',
                    hoverColor: '{text.hover.color}',
                },
                formField: {
                    background: '{surface.950}',
                    borderColor: '{surface.600}',
                    hoverBorderColor: '{surface.500}',
                    focusBorderColor: '{primary.color}',
                    color: '{surface.50}',
                    placeholderColor: '{surface.400}',
                    iconColor: '{surface.400}',
                    shadow: 'none',
                },
                overlay: {
                    select: { background: '{surface.900}', borderColor: '{surface.600}', color: '{text.color}' },
                    popover: { background: '{surface.900}', borderColor: '{surface.600}', color: '{text.color}' },
                    modal: { background: '{surface.900}', borderColor: '{surface.600}', color: '{text.color}' },
                },
            },
        },
    },
    // Shadows belong to overlays only (Dialog, Popover, Toast keep theirs). Card is the one
    // content component whose Aura tokens carry a shadow; Panel and DataTable have none to remove.
    components: {
        card: {
            root: {
                shadow: 'none',
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
