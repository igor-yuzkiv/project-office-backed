<script setup lang="ts">
import { ref } from 'vue'

// `fill`: the content wants exactly the canvas height (an editor that scrolls inside itself),
// so the column gives up the reading margin at the bottom.
defineProps<{ fill?: boolean }>()

const scrollElement = ref<HTMLElement>()

// The host may need to put the reader back where they were after swapping what is on the canvas.
defineExpose({ scrollElement })
</script>

<template>
    <!-- The canvas scrolls itself, so the host has to give it a bounded height. -->
    <div ref="scrollElement" class="bg-page document-canvas min-h-0 flex flex-1 flex-col overflow-y-auto">
        <!-- The same column as the Comments tab, so the document does not jump between tabs.
             flex-1 min-h-0: content that wants exactly the canvas height gets it; prose keeps its
             natural height and overflows into the canvas scroll. -->
        <div class="gap-4 page-container min-h-0 min-w-0 flex max-w-[960px] flex-1 flex-col" :class="{ '!pb-6': fill }">
            <slot name="start" />
            <slot />
        </div>
    </div>
</template>
