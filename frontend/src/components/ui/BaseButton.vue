<template>
  <component
    :is="tag"
    class="base-button"
    :class="[`base-button--${variant}`, { 'is-block': block }]"
    v-bind="$attrs"
  >
    <slot />
  </component>
</template>

<script setup>
defineProps({
  tag: {
    type: String,
    default: 'button',
  },
  variant: {
    type: String,
    default: 'primary',
  },
  block: {
    type: Boolean,
    default: false,
  },
});
</script>
