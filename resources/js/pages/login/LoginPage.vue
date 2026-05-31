<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import { useAuthStore } from '@/app/stores/use.auth.store'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref<string | null>(null)

async function handleLogin() {
    errorMessage.value = null
    loading.value = true
    try {
        await authStore.login({ email: email.value, password: password.value })
        await router.push({ name: 'home' })
    } catch {
        errorMessage.value = 'Invalid email or password.'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="bg-surface-50 dark:bg-surface-950 flex min-h-screen items-center justify-center">
        <div class="max-w-sm px-4 w-full">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-semibold text-surface-900 dark:text-surface-0">Task Manager</h1>
            </div>

            <div
                class="rounded-xl border-surface-200 bg-white p-6 shadow-sm dark:border-surface-700 dark:bg-surface-900 border"
            >
                <form class="gap-5 flex flex-col" @submit.prevent="handleLogin">
                    <div class="gap-1.5 flex flex-col">
                        <label for="email" class="text-sm font-medium text-surface-700 dark:text-surface-300">
                            Email
                        </label>
                        <InputText
                            id="email"
                            v-model="email"
                            type="email"
                            placeholder="you@example.com"
                            autocomplete="email"
                            class="w-full"
                            :invalid="!!errorMessage"
                        />
                    </div>

                    <div class="gap-1.5 flex flex-col">
                        <label for="password" class="text-sm font-medium text-surface-700 dark:text-surface-300">
                            Password
                        </label>
                        <Password
                            id="password"
                            v-model="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            class="w-full"
                            :feedback="false"
                            toggle-mask
                            :invalid="!!errorMessage"
                        />
                    </div>

                    <p v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</p>

                    <Button type="submit" label="Sign In" class="w-full" :loading="loading" />
                </form>
            </div>
        </div>
    </div>
</template>
