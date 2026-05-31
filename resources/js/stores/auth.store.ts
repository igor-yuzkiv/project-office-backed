import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { fetchCsrfCookieRequest, fetchUserRequest, loginRequest, logoutRequest } from '@/entities/user/api'
import type { ILoginCredentials, IUser } from '@/entities/user/types'

export const useAuthStore = defineStore('auth', () => {
    const user = ref<IUser | null>(null)
    const initialized = ref(false)

    const isAuthenticated = computed(() => user.value !== null)

    async function initialize() {
        if (initialized.value) return
        try {
            const { data } = await fetchUserRequest()
            user.value = data
        } catch {
            user.value = null
        } finally {
            initialized.value = true
        }
    }

    async function login(credentials: ILoginCredentials) {
        await fetchCsrfCookieRequest()
        const { data } = await loginRequest(credentials)
        user.value = data
    }

    async function logout() {
        await logoutRequest()
        user.value = null
    }

    return { user, initialized, isAuthenticated, initialize, login, logout }
})
