<template>
  <RouterView />
</template>

<script setup lang="ts">
import { RouterView } from 'vue-router'
</script>

<style>
/* Smooth Scrolling */
html {
  scroll-behavior: smooth;
}

/* Normal System Cursor Behavior */
body {
  cursor: default;
}
a, button, [role="button"] {
  cursor: pointer;
}
input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="tel"], textarea {
  cursor: text;
}


/* Scroll Reveal Animations */
.reveal-el {
  opacity: 0;
  transform: translateY(40px);
  transition: all 1s cubic-bezier(0.19, 1, 0.22, 1);
}
.reveal-el.is-revealed {
  opacity: 1;
  transform: translateY(0);
}

/* Image Reveal Clip Path */
.reveal-image-container {
  clip-path: polygon(0 100%, 100% 100%, 100% 100%, 0 100%);
  transition: clip-path 1.5s cubic-bezier(0.19, 1, 0.22, 1);
}
.reveal-image-container.is-revealed {
  clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
}
.reveal-image {
  transform: scale(1.2);
  transition: transform 2s cubic-bezier(0.19, 1, 0.22, 1);
}
.reveal-image-container.is-revealed .reveal-image {
  transform: scale(1);
}
</style>
