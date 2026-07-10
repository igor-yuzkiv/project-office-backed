import { useMutation } from '@tanstack/vue-query'
import { uploadUserAvatarRequest } from '../api'
import { useAuthStore } from '@/app/stores/use.auth.store'

export function useUploadUserAvatarMutation() {
    const authStore = useAuthStore()

    return useMutation({
        mutationFn: (file: File) => uploadUserAvatarRequest(file),
        onSuccess: ({ data }) => {
            authStore.user = data
        },
    })
}
