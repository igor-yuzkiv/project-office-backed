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
 * The design tokens of the redesign (see _tmp/context/ui-redesign/design.css) expressed as the
 * PrimeVue semantic scheme. The surface scale carries the page/canvas/hover/selected/line steps so
 * that the `surface-*` Tailwind utilities from tailwindcss-primeui pick them up without any change
 * in components; the semantic aliases below point at the same steps so PrimeVue components agree.
 *
 * Light: 0 page · 50 canvas · 100 hover · 200 line · 300 line-2 · 400 ink-3 · 500 ink-2 · 900 ink.
 * Dark:  950 canvas · 900 page · 800 hover · 700 selected · 600 line-2 · 500 ink-3 · 400 ink-2 · 50 ink.
 * The dark hairline (`--line`, #1c232c) sits between 800 and 700 and is set on the aliases directly.
 */
const MyPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: '{blue.50}',
            100: '{blue.100}',
            200: '{blue.200}',
            300: '{blue.300}',
            400: '{blue.400}',
            500: '#3b82f6',
            600: '{blue.600}',
            700: '{blue.700}',
            800: '{blue.800}',
            900: '{blue.900}',
            950: '{blue.950}',
        },
        colorScheme: {
            light: {
                surface: {
                    0: '#ffffff',
                    50: '#f8fafc',
                    100: '#eef2f6',
                    200: '#e8ecf1',
                    300: '#d5dbe3',
                    400: '#94a3b8',
                    500: '#64748b',
                    600: '{slate.600}',
                    700: '{slate.700}',
                    800: '{slate.800}',
                    900: '#0f172a',
                    950: '{slate.950}',
                },
                primary: {
                    color: '{primary.500}',
                    contrastColor: '#ffffff',
                    hoverColor: '{primary.600}',
                    activeColor: '{primary.700}',
                },
                highlight: {
                    background: '#e6efff',
                    focusBackground: '{primary.100}',
                    color: '{primary.700}',
                    focusColor: '{primary.800}',
                },
                text: {
                    color: '{surface.900}',
                    hoverColor: '{surface.950}',
                    mutedColor: '{surface.500}',
                    hoverMutedColor: '{surface.600}',
                },
                content: {
                    background: '{surface.0}',
                    hoverBackground: '{surface.100}',
                    borderColor: '{surface.200}',
                    color: '{text.color}',
                    hoverColor: '{text.hover.color}',
                },
                formField: {
                    background: '{surface.0}',
                    borderColor: '{surface.300}',
                    hoverBorderColor: '{surface.400}',
                    focusBorderColor: '{primary.color}',
                    color: '{surface.900}',
                    placeholderColor: '{surface.400}',
                    iconColor: '{surface.400}',
                    shadow: 'none',
                },
                overlay: {
                    select: { background: '{surface.0}', borderColor: '{surface.200}', color: '{text.color}' },
                    popover: { background: '{surface.0}', borderColor: '{surface.200}', color: '{text.color}' },
                    modal: { background: '{surface.0}', borderColor: '{surface.200}', color: '{text.color}' },
                },
            },
            dark: {
                surface: {
                    0: '#ffffff',
                    50: '#e6ebf1',
                    100: '{slate.200}',
                    200: '{slate.300}',
                    300: '{slate.400}',
                    400: '#8b97a6',
                    500: '#5c6877',
                    600: '#2a333e',
                    700: '#1f2731',
                    800: '#171d26',
                    900: '#11171e',
                    950: '#12181f',
                },
                primary: {
                    color: '{primary.500}',
                    contrastColor: '#ffffff',
                    hoverColor: '{primary.400}',
                    activeColor: '{primary.300}',
                },
                highlight: {
                    background: '#163464',
                    focusBackground: '#1f4380',
                    color: '#7fb0ff',
                    focusColor: '#a9c8ff',
                },
                text: {
                    color: '{surface.50}',
                    hoverColor: '{surface.0}',
                    mutedColor: '{surface.400}',
                    hoverMutedColor: '{surface.300}',
                },
                content: {
                    background: '{surface.900}',
                    hoverBackground: '{surface.800}',
                    borderColor: '#1c232c',
                    color: '{text.color}',
                    hoverColor: '{text.hover.color}',
                },
                formField: {
                    background: '{surface.900}',
                    borderColor: '{surface.600}',
                    hoverBorderColor: '{surface.500}',
                    focusBorderColor: '{primary.color}',
                    color: '{surface.50}',
                    placeholderColor: '{surface.500}',
                    iconColor: '{surface.500}',
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
