<template>
  <label class="field">
    <span v-if="label" class="field__label">{{ label }}</span>
    <textarea
      class="field__control field__control--textarea"
      :class="{ 'has-error': error }"
      :value="modelValue"
      v-bind="$attrs"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <small v-if="error" class="field__error">{{ error }}</small>
  </label>
</template>

<script setup>
defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  label: {
    type: String,
    default: '',
  },
  error: {
    type: String,
    default: '',
  },
});

defineEmits(['update:modelValue']);
</script>
