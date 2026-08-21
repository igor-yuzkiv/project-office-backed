<script setup lang="ts">
import { onMounted, watch, useTemplateRef } from 'vue'

const title = defineModel<string>({ required: true })

const heading = useTemplateRef<HTMLHeadingElement>('heading')

// The element is uncontrolled while focused: writing back into it on every
// keystroke would move the caret to the end of the line.
function syncFromModel() {
    if (heading.value && heading.value.textContent !== title.value) {
        heading.value.textContent = title.value
    }
}

function onInput() {
    // A title is one line: pasted or shift-entered breaks collapse to spaces.
    title.value = (heading.value?.textContent ?? '').replace(/\s*\n+\s*/g, ' ')
}

function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Enter') {
        event.preventDefault()
        heading.value?.blur()
    }
}

// Not `immediate`: that callback runs before the element exists.
watch(title, syncFromModel, { flush: 'post' })

onMounted(syncFromModel)
</script>

<template>
    <h1
        ref="heading"
        contenteditable="plaintext-only"
        role="textbox"
        aria-label="Document title"
        class="text-surface-900 dark:text-surface-0 hover:border-surface-300 focus:border-primary-400 -mx-1 px-1 rounded text-xl font-semibold border border-transparent outline-none"
        @input="onInput"
        @keydown="onKeydown"
    />
</template>
