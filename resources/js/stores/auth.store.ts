import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { authApi } from '@/entities/user/api'
import type { ILoginCredentials, IUser } from '@/entities/user/types'

export const useAuthStore = defineStore('auth', () => {
    const user = ref<IUser | null>(null)
    const initialized = ref(false)

    const isAuthenticated = computed(() => user.value !== null)

    async function initialize() {
        if (initialized.value) return
        try {
            const { data } = await authApi.getUser()
            user.value = data.data
        } catch {
            user.value = null
        } finally {
            initialized.value = true
        }
    }

    async function login(credentials: ILoginCredentials) {
        await authApi.getCsrfCookie()
        const { data } = await authApi.login(credentials)
        user.value = data.data
    }

    async function logout() {
        await authApi.logout()
        user.value = null
    }

    return { user, initialized, isAuthenticated, initialize, login, logout }
})
