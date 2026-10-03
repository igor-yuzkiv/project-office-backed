<script setup lang="ts">
import { useId } from 'vue'

// Gradient ids must be unique per instance: the sidebar and the login page can both render the mark.
const faceId = `wb-face-${useId()}`
const bladeId = `wb-blade-${useId()}`
</script>

<template>
    <!-- The anvil face and the blade of public/favicon.svg, without the tile, so it sits on either theme. -->
    <svg viewBox="80 120 352 312" role="img" aria-label="Workbench">
        <defs>
            <linearGradient :id="faceId" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#ffb35c" />
                <stop offset="0.55" stop-color="#f26b0f" />
                <stop offset="1" stop-color="#c2410c" />
            </linearGradient>
            <linearGradient :id="bladeId" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#c9ccd6" />
                <stop offset="1" stop-color="#6c707e" />
            </linearGradient>
        </defs>
        <path d="M88 132H424Q412 206 340 220H172Q100 206 88 132Z" :fill="`url(#${faceId})`" />
        <path d="M196 244H316L302 398Q256 424 210 404Z" :fill="`url(#${bladeId})`" />
    </svg>
</template>
