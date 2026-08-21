<script setup lang="ts">
import EmojiPickerCatalog from 'vue3-emoji-picker'
import type { EmojiExt } from 'vue3-emoji-picker'

const emit = defineEmits<{
    (e: 'select', emoji: string): void
}>()

// The package hands back a record whose `i` field is the glyph. That naming is the
// package's business, and this wrapper is where it stops.
function onSelect(emoji: EmojiExt) {
    emit('select', emoji.i)
}
</script>

<template>
    <EmojiPickerCatalog native @select="onSelect" />
</template>
