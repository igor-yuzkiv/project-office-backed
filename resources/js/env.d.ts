/// <reference types="vite/client" />

interface ImportMetaEnv {
    readonly VITE_APP_NAME: string
    readonly VITE_API_BASE_URL: string

    readonly VITE_REVERB_APP_KEY: string
    readonly VITE_REVERB_HOST: string
    readonly VITE_REVERB_WS_PORT?: number
    readonly VITE_REVERB_WSS_PORT?: number
    readonly VITE_REVERB_SCHEME?: string
    readonly VITE_CLIENT_BROADCAST_CHANNEL: string
}
