<script setup lang="ts">
import { computed } from 'vue'
import EmojiPickerCatalog from 'vue3-emoji-picker'
import type { EmojiExt } from 'vue3-emoji-picker'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'

const emit = defineEmits<{
    (e: 'select', emoji: string): void
}>()

const themeStore = useAppThemeStore()

const theme = computed(() => (themeStore.isDark ? 'dark' : 'light'))

// The package hands back a record whose `i` field is the glyph. That naming is the
// package's business, and this wrapper is where it stops.
function onSelect(emoji: EmojiExt) {
    emit('select', emoji.i)
}
</script>

<template>
    <EmojiPickerCatalog native :theme="theme" @select="onSelect" />
</template>
