<script setup lang="ts">
import Button from 'primevue/button'
import { httpClient } from '@/shared/api'

async function test() {
    httpClient.get('/sanctum/csrf-cookie', { baseURL: '/' }).then((response) => {
        console.log(response)

        httpClient
            .post('login', {
                email: 'iy@crmoz.com',
                password: 'qwert123',
            })
            .then((response) => {
                console.log('login', response)
            })
    })
}

function test2() {
    httpClient.get('user').then((response) => {
        console.log('user', response)
    })
}
</script>

<template>
    <Button @click="test" label="test" />
    <Button @click="test2" label="test2" />
</template>

<style scoped></style>
