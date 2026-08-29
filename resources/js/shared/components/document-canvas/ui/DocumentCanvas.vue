<script setup lang="ts">
import { ref } from 'vue'

const scrollElement = ref<HTMLElement>()

// The host may need to put the reader back where they were after swapping what is on the canvas.
defineExpose({ scrollElement })
</script>

<template>
    <!-- The canvas scrolls itself, so the host has to give it a bounded height. -->
    <div ref="scrollElement" class="p-6 document-canvas min-h-0 flex flex-1 flex-col items-center overflow-y-auto">
        <!-- flex-1 min-h-0: content that wants exactly the canvas height (an editor that scrolls
             inside itself) gets it; cards keep their natural height and overflow into the canvas
             scroll as before. -->
        <div class="gap-3 max-w-7xl min-h-0 min-w-0 flex w-full flex-1 flex-col">
            <slot name="start" />
            <slot />
            <slot name="end" />
        </div>
    </div>
</template>

<style>
/* The content reads as a sheet, so the surface behind it is a drafting canvas. */
.document-canvas {
    background-color: var(--p-surface-100);
    background-image: radial-gradient(circle, var(--p-surface-300) 1px, transparent 1px);
    background-size: 18px 18px;
}

.dark .document-canvas {
    background-color: var(--p-surface-950);
    background-image: radial-gradient(circle, var(--p-surface-800) 1px, transparent 1px);
}
</style>
