<template>
    <!-- The canvas scrolls itself, so the host has to give it a bounded height. -->
    <div class="p-6 document-canvas min-h-0 flex flex-1 flex-col items-center overflow-y-auto">
        <div class="gap-3 max-w-7xl min-w-0 flex w-full flex-col">
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
