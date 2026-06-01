<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import { Icon } from '@iconify/vue'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { APP_NAME } from '@/app/config'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref<string | null>(null)

const features = [
    'Kanban boards, timelines, and list views',
    'Advanced reporting and portfolio analytics',
    'Role-based access and audit logs',
    'Real-time collaboration across teams',
]

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
    <div class="flex h-screen w-full">
        <!-- Left panel -->
        <div class="bg-surface-900 px-12 py-12 lg:flex hidden w-2/5 shrink-0 flex-col justify-between">
            <div class="gap-2.5 flex items-center">
                <img src="/logo.png" alt="Logo" class="h-8 w-auto" />
                <span class="text-lg font-semibold tracking-tight text-white">{{ APP_NAME }}</span>
            </div>

            <div class="gap-8 flex flex-col">
                <div class="gap-4 flex flex-col">
                    <h1 class="text-3xl font-semibold leading-snug text-white">
                        Plan, track, and deliver with confidence.
                    </h1>
                    <p class="text-sm leading-relaxed text-surface-400">
                        Brings your projects, tasks, and teams into one unified workspace — built for teams that move
                        fast.
                    </p>
                </div>

                <ul class="gap-3 flex flex-col">
                    <li
                        v-for="feature in features"
                        :key="feature"
                        class="gap-2.5 text-sm text-surface-400 flex items-center"
                    >
                        <Icon icon="heroicons:check-circle" class="h-3.5 w-3.5 text-primary-400 shrink-0" />
                        {{ feature }}
                    </li>
                </ul>
            </div>

            <div class="gap-4 text-xs text-surface-500 flex items-center">
                <a href="#" class="hover:text-surface-300 transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-surface-300 transition-colors">Terms of Service</a>
                <a href="#" class="hover:text-surface-300 transition-colors">Security</a>
            </div>
        </div>

        <!-- Right panel -->
        <div class="bg-surface-50 px-6 py-12 dark:bg-surface-950 flex flex-1 items-center justify-center">
            <div class="max-w-sm gap-8 flex w-full flex-col">
                <div class="gap-1.5 flex flex-col">
                    <h2 class="text-2xl font-semibold text-surface-900 dark:text-surface-0">
                        Sign in to {{ APP_NAME }}
                    </h2>
                    <p class="text-sm text-surface-500">Enter your credentials to access your workspace.</p>
                </div>

                <form class="gap-4 flex flex-col" @submit.prevent="handleLogin">
                    <div class="gap-1.5 flex flex-col">
                        <label for="email" class="text-sm font-medium text-surface-700 dark:text-surface-300">
                            Email address
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
                        <div class="flex items-center justify-between">
                            <label for="password" class="text-sm font-medium text-surface-700 dark:text-surface-300">
                                Password
                            </label>
                        </div>
                        <Password
                            id="password"
                            v-model="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            :feedback="false"
                            toggle-mask
                            fluid
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
